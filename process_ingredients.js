let RECIPES = [];
let STORE_INVENTORY = {};
let userIngredients = [];

const input = document.getElementById('ingredient-input');

input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        addIngredient();
    }
});

/* normalize
   purpose: trims the ingredient string and turns to all lower case
   parameters: str - the ingredient
   returns: a lowercase string with surrounding spaces removed
*/
function normalize(str) {
    return str.trim().toLowerCase();
}

/* hasIngredient
   purpose: check whether an ingredient exists in the current user list by 
            verifying that the index is not -1
   parameters: item - the ingredient name to look for
   returns: true when the ingredient is already present, otherwise false
*/
function hasIngredient(item) {
    return userIngredients.indexOf(item) !== -1;
}

/* showElement
   purpose: replaces the hidden element with the actual data
   parameters: id - the DOM element id to show
   returns: nothing
*/
function showElement(id) {
    document.getElementById(id).className = document.getElementById(id).className.replace('hidden', '').trim();
}

/* hideElement
   purpose: hides an element by appending the hidden CSS class
   parameters: id - the DOM element id to hide
   returns: nothing
*/
function hideElement(id) {
    const el = document.getElementById(id);
    // Avoid adding duplicate hidden classes
    if (el.className.indexOf('hidden') === -1) {
        el.className += ' hidden';
    }
}

/* addIngredient
   purpose: adds a new ingredient
   returns: nothing
*/
function addIngredient() {
    const val = normalize(input.value);

    // ignores empty values
    if (!val) {
        return;
    }

    // ignore duplicates
    if (userIngredients.indexOf(val) !== -1) {
        input.value = '';
        return;
    }

    // refreshes the tag UI
    userIngredients.push(val);
    renderTags();
    input.value = '';
    input.focus();
}

/* removeIngredient
   purpose: removes an ingredient from the current user list
   parameters: ingredient - the ingredient name to remove
   returns: nothing
*/
function removeIngredient(ingredient) {
    userIngredients = userIngredients.filter(function (item) {
        return item !== ingredient;
    });
    // rebuild the tag display
    renderTags();
}

/* renderTags
   purpose: display the current selected ingredients as removable tags
   returns: nothing
*/
function renderTags() {
    const container = document.getElementById('tag-container');
    let html = '';
    let i;

    // loop through ingredients and write HTML into the tag container
    for (i = 0; i < userIngredients.length; i++) {
        html += '<span class="tag">' + userIngredients[i]
            + '<span class="tag-remove" onclick="removeIngredient(\'' + userIngredients[i] + '\')" title="Remove">&times;</span>'
            + '</span>';
    }

    container.innerHTML = html;
}

/* loadData
   purpose: loads recipes and stores JSON files into memory
   parameters: done - optional callback to run after loading completes
   returns: nothing
*/
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
        // print errors to console
        .catch(function (error) {
            console.log('Error loading JSON data:', error);
        });
}

/* findRecipes
   purpose: calculates recipe matches based on the user's ingredients
   returns: nothing
*/
function findRecipes() {
    if (userIngredients.length === 0) {
        alert('Please add at least one ingredient first!');
        return;
    }

    // Ensure the recipe data is loaded before searching
    // If data is not loaded yet, load it and retry once it is ready
    if (RECIPES.length === 0) {
        loadData(function () {
            findRecipes();
        });
        return;
    }

    const resultsGrid = document.getElementById('results-grid');
    const noResults = document.getElementById('no-results');

    // Prepare the UI for new results and show the loading state.
    showElement('results-section');
    showElement('loading-state');
    hideElement('no-results');
    resultsGrid.innerHTML = '';
    location.href = '#results-section';

    /* This method is used to schedule the execution of a function after a 
       specified delay
       Reference: https://www.w3schools.com/JSREF/met_win_settimeout.asp
    */
    setTimeout(function () {
        // Build a list of recipe matches, including which ingredients
        // the user has and which are missing.
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

        // Only keep the recipes where the user has at least one ingredient
        matches = matches.filter(function (recipe) {
            return recipe.score > 0;
        });

        // Sort results by best match first (highest percentage of ingredients owned) (Reference https://www.w3schools.com/js/js_array_sort.asp)
        matches.sort(function (a, b) {
            return b.score - a.score;
        });

        hideElement('loading-state');

        // Update the heading with the number of matching recipes found
        if (matches.length > 0) {
            if (matches.length === 1) {
                document.getElementById('results-heading').innerHTML = matches.length + ' recipe found for you';
            } else {
                document.getElementById('results-heading').innerHTML = matches.length + ' recipes found for you';
            }
        } else {
            document.getElementById('results-heading').innerHTML = 'No recipes found';
        }

        if (matches.length === 0) {
            showElement('no-results');
            return;
        }

        // Render the recipe cards for each matching recipe.
        resultsGrid.innerHTML = matches.map(function (recipe, i) {
            return buildRecipeCard(recipe, i);
        }).join('');
    }, 500);
}

/* buildRecipeCard
   purpose: builds the HTML for a recipe card from recipe match data
   parameters: recipe - the recipe object
               index - the index of the recipe in the results list
   returns: the HTML string for the recipe card
*/
function buildRecipeCard(recipe, index) {
    const pct = Math.round(recipe.score * 100);
    let haveItems = '';
    let missingItems = '';
    let extraSection = '';
    let i;

    // calculate match percentage and missing ingredient sections
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

/* buildStoreInfo
   purpose: builds the HTML for store availability of missing ingredients
   parameters: missingItems - array of missing ingredient names
   returns: the HTML string for matching stores and item counts
*/
function buildStoreInfo(missingItems) {
    let store;
    let inventory;
    let available;
    let html = '';

    if (missingItems.length === 0) {
        return '';
    }

    // use STORE_INVENTORY to identify which stores carry items
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

/* shopOnInstacart
   purpose: opens Instacart search results for missing ingredients
   parameters: items - ingredients to look for
   returns: nothing
*/
function shopOnInstacart(items) {
    const list = items.split('|');
    const query = encodeURIComponent(list.join(' '));
    const url = 'https://www.instacart.com/store/s?k=' + query;
    // open the URL in a new browser tab
    window.open(url, '_blank');
}

/* clearResults
   purpose: resets ingredient selection and clear displayed recipe results
   returns: nothing
*/
function clearResults() {
    hideElement('results-section');
    document.getElementById('results-grid').innerHTML = '';
    userIngredients = [];
    renderTags();
    // scroll the page to the top 
    // (Reference: https://www.w3schools.com/jsref/met_win_scrollto.asp)
    window.scrollTo(0, 0);
}

/* capitalize
   purpose: capitalizes the first letter of a string
   parameters: str - the string to capitalize
   returns: the capitalized string
*/
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

window.onload = function () {
    loadData();
};