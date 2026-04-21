let RECIPES = [];
let STORE_INVENTORY = {};
let userIngredients = [];

const input = document.getElementById('ingredient-input');

input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        addIngredient();
    }
});

function normalize(str) {
    return str.trim().toLowerCase();
}

function hasIngredient(item) {
    return userIngredients.indexOf(item) !== -1;
}

function showElement(id) {
    document.getElementById(id).className = document.getElementById(id).className.replace('hidden', '').trim();
}

function hideElement(id) {
    const el = document.getElementById(id);
    if (el.className.indexOf('hidden') === -1) {
        el.className += ' hidden';
    }
}

function addIngredient() {
    const val = normalize(input.value);

    if (!val) {
        return;
    }

    if (userIngredients.indexOf(val) !== -1) {
        input.value = '';
        return;
    }

    userIngredients.push(val);
    renderTags();
    input.value = '';
    input.focus();
}

function removeIngredient(ing) {
    userIngredients = userIngredients.filter(function (item) {
        return item !== ing;
    });
    renderTags();
}

function renderTags() {
    const container = document.getElementById('tag-container');
    let html = '';
    let i;

    for (i = 0; i < userIngredients.length; i++) {
        html += '<span class="tag">' + userIngredients[i]
            + '<span class="tag-remove" onclick="removeIngredient(\'' + userIngredients[i] + '\')" title="Remove">&times;</span>'
            + '</span>';
    }

    container.innerHTML = html;
}

function loadData(done) {
    fetch('recipes.json')
        .then(function (res) {
            return res.text();
        })
        .then(function (data) {
            RECIPES = JSON.parse(data);
            return fetch('stores.json');
        })
        .then(function (res) {
            return res.text();
        })
        .then(function (data) {
            STORE_INVENTORY = JSON.parse(data);
            if (done) {
                done();
            }
        })
        .catch(function (error) {
            console.log('Error loading JSON data:', error);
        });
}

function findRecipes() {
    if (userIngredients.length === 0) {
        alert('Please add at least one ingredient first!');
        return;
    }

    if (RECIPES.length === 0) {
        loadData(function () {
            findRecipes();
        });
        return;
    }

    const resultsGrid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');

    showElement('results-section');
    showElement('loading-state');
    hideElement('no-results');
    resultsGrid.innerHTML = '';
    location.href = '#results-section';

    setTimeout(function () {
        let matches = RECIPES.map(function (recipe) {
            const have = recipe.ingredients.filter(function (i) {
                return hasIngredient(i);
            });

            const missing = recipe.ingredients.filter(function (i) {
                return !hasIngredient(i);
            });

            return {
                name: recipe.name,
                ingredients: recipe.ingredients,
                have: have,
                missing: missing,
                score: have.length / recipe.ingredients.length
            };
        });

        matches = matches.filter(function (recipe) {
            return recipe.score > 0;
        });

        matches.sort(function (a, b) {
            return b.score - a.score;
        });

        hideElement('loading-state');

        if (matches.length > 0) {
            document.getElementById('results-heading').innerHTML = matches.length
                + (matches.length === 1 ? ' recipe found for you' : ' recipes found for you');
        } else {
            document.getElementById('results-heading').innerHTML = 'No recipes found';
        }

        if (matches.length === 0) {
            showElement('no-results');
            return;
        }

        resultsGrid.innerHTML = matches.map(function (recipe, i) {
            return buildRecipeCard(recipe, i);
        }).join('');
    }, 500);
}

function buildRecipeCard(recipe, index) {
    const pct = Math.round(recipe.score * 100);
    let haveItems = '';
    let missingItems = '';
    let extraSection = '';
    let i;

    for (i = 0; i < recipe.have.length; i++) {
        haveItems += '<li><span class="dot dot-have"></span> ' + capitalize(recipe.have[i])
            + ' <span style="color:#3AAD6E;font-size:0.75rem;">&#10003;</span></li>';
    }

    for (i = 0; i < recipe.missing.length; i++) {
        missingItems += '<li><span class="dot dot-missing"></span> ' + capitalize(recipe.missing[i]) + '</li>';
    }

    if (recipe.missing.length === 0) {
        extraSection = '<p style="font-size:0.9rem;color:#3AAD6E;font-weight:700;">You have everything!</p>';
    } else {
        extraSection = '<div class="stores-section">'
            + '<h4>Where to buy the rest</h4>'
            + buildStoreInfo(recipe.missing)
            + '</div>'
            + '<button class="btn btn-primary shop-btn" onclick="shopOnInstacart(\'' + recipe.missing.join('|') + '\')">'
            + 'Shop Missing Ingredients on Instacart</button>';

        if (buildStoreInfo(recipe.missing) === '') {
            extraSection = '<div class="stores-section">'
                + '<h4>Where to buy the rest</h4>'
                + '<p style="font-size:0.85rem;color:#6b7280;">No local store matches found.</p>'
                + '</div>'
                + '<button class="btn btn-primary shop-btn" onclick="shopOnInstacart(\'' + recipe.missing.join('|') + '\')">'
                + 'Shop Missing Ingredients on Instacart</button>';
        }
    }

    return '<div class="recipe-card">'
        + '<div class="recipe-card-header">'
        + '<h3>' + recipe.name + '</h3>'
        + '<span class="match-badge">' + pct + '% match</span>'
        + '</div>'
        + '<div class="recipe-card-body">'
        + '<ul class="ingredient-list">' + haveItems + missingItems + '</ul>'
        + extraSection
        + '</div>'
        + '</div>';
}

function buildStoreInfo(missingItems) {
    let store;
    let inventory;
    let available;
    let html = '';

    if (missingItems.length === 0) {
        return '';
    }

    for (store in STORE_INVENTORY) {
        inventory = STORE_INVENTORY[store];
        available = missingItems.filter(function (item) {
            return inventory.indexOf(item) !== -1;
        });

        if (available.length > 0) {
            html += '<div class="store-item">'
                + '<span class="store-name">' + store + '</span>'
                + '<span class="store-items-count">' + available.length + ' of ' + missingItems.length + ' missing items</span>'
                + '</div>';
        }
    }

    return html;
}

function shopOnInstacart(items) {
    const list = items.split('|');
    const query = encodeURIComponent(list.join(' '));
    const url = 'https://www.instacart.com/store/s?k=' + query;
    window.open(url, '_blank');
}

function clearResults() {
    hideElement('results-section');
    document.getElementById('results-grid').innerHTML = '';
    userIngredients = [];
    renderTags();
    window.scrollTo(0, 0);
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

window.onload = function () {
    loadData();
};