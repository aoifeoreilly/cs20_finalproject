let RECIPES = [];
let STORE_INVENTORY = {};

// State
let userIngredients = [];

// Input handling
const input = document.getElementById('ingredient-input');

input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        addIngredient();
    }
});

function normalize(str) {
    return str.trim().toLowerCase();
}

function addIngredient() {
    const val = normalize(input.value);

    if (!val) return;

    if (userIngredients.includes(val)) {
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

    container.innerHTML = userIngredients.map(function (ing) {
        return `
            <span class="tag">
                ${ing}
                <span class="tag-remove" onclick="removeIngredient('${ing}')" title="Remove">&times;</span>
            </span>
        `;
    }).join('');
}

async function loadData() {
    try {
        const recipeResponse = await fetch('recipes.json');
        RECIPES = await recipeResponse.json();

        const storeResponse = await fetch('stores.json');
        STORE_INVENTORY = await storeResponse.json();
    } catch (error) {
        console.log('Error loading JSON data:', error);
    }
}

async function findRecipes() {
    if (userIngredients.length === 0) {
        alert('Please add at least one ingredient first!');
        return;
    }

    if (RECIPES.length === 0) {
        await loadData();
    }

    const resultsSection = document.getElementById('results-section');
    const loadingState = document.getElementById('loading-state');
    const resultsGrid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');

    resultsSection.classList.remove('hidden');
    loadingState.classList.remove('hidden');
    resultsGrid.innerHTML = '';
    noResults.classList.add('hidden');
    resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    setTimeout(function () {
        loadingState.classList.add('hidden');

        const matches = RECIPES.map(function (recipe) {
            const have = recipe.ingredients.filter(function (i) {
                return userIngredients.includes(i);
            });

            const missing = recipe.ingredients.filter(function (i) {
                return !userIngredients.includes(i);
            });

            const score = have.length / recipe.ingredients.length;

            return {
                name: recipe.name,
                ingredients: recipe.ingredients,
                have: have,
                missing: missing,
                score: score
            };
        })
        .filter(function (recipe) {
            return recipe.score > 0;
        })
        .sort(function (a, b) {
            return b.score - a.score;
        });

        document.getElementById('results-heading').textContent =
            matches.length > 0
                ? matches.length + (matches.length === 1 ? ' recipe found for you' : ' recipes found for you')
                : 'No recipes found';

        if (matches.length === 0) {
            noResults.classList.remove('hidden');
            return;
        }

        resultsGrid.innerHTML = matches.map(function (recipe, i) {
            return buildRecipeCard(recipe, i);
        }).join('');
    }, 500);
}

function buildRecipeCard(recipe, index) {
    const pct = Math.round(recipe.score * 100);
    const delay = index * 80;

    const haveItems = recipe.have.map(function(i) {
        return `<li><span class="dot dot-have"></span> ${capitalize(i)} <span style="color:#3AAD6E;font-size:0.75rem;">✓</span></li>`;
    }).join('');

    const missingItems = recipe.missing.map(function(i) {
        return `<li><span class="dot dot-missing"></span> ${capitalize(i)}</li>`;
    }).join('');

    const storeHtml = buildStoreInfo(recipe.missing);

    let extraSection = '';

    if (recipe.missing.length === 0) {
        extraSection = `<p style="font-size:0.9rem;color:#3AAD6E;font-weight:700;">You have everything!</p>`;
    } else {
        extraSection = `
            <div class="stores-section">
                <h4>Where to buy the rest</h4>
                ${storeHtml !== '' ? storeHtml : '<p style="font-size:0.85rem;color:#6b7280;">No local store matches found.</p>'}
            </div>

            <button class="btn btn-primary shop-btn" onclick='shopOnInstacart(${JSON.stringify(recipe.missing)})'>
                Shop Missing Ingredients on Instacart
            </button>
        `;
    }

    return `
        <div class="recipe-card" style="animation-delay:${delay}ms">
            <div class="recipe-card-header">
                <h3>${recipe.name}</h3>
                <span class="match-badge">${pct}% match</span>
            </div>
            <div class="recipe-card-body">
                <ul class="ingredient-list">
                    ${haveItems}
                    ${missingItems}
                </ul>
                ${extraSection}
            </div>
        </div>
    `;
}

function buildStoreInfo(missingItems) {
    if (missingItems.length === 0) return '';

    return Object.entries(STORE_INVENTORY).map(function ([store, inventory]) {
        const available = missingItems.filter(function (i) {
            return inventory.includes(i);
        });

        if (available.length === 0) return '';

        return `
            <div class="store-item">
                <span class="store-name">${store}</span>
                <span class="store-items-count">${available.length} of ${missingItems.length} missing items</span>
            </div>
        `;
    }).filter(Boolean).join('');
}

function shopOnInstacart(items) {
    const query = encodeURIComponent(items.join(" "));
    const url = `https://www.instacart.com/store/s?k=${query}`;
    window.open(url, "_blank");
}

function clearResults() {
    document.getElementById('results-section').classList.add('hidden');
    document.getElementById('results-grid').innerHTML = '';
    userIngredients = [];
    renderTags();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

window.onload = loadData;