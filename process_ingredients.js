const RECIPES = fetch('recipe.json');
const STORE_INVENTORY = fetch('stores.json');

// State 
let userIngredients = [];

// Input handling
const input = document.getElementById('ingredient-input');

input.addEventListener('keydown', e => {
    if (e.key === 'Enter') addIngredient();
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
    userIngredients = userIngredients.filter(i => i !== ing);
    renderTags();
}

function renderTags() {
    const container = document.getElementById('tag-container');
    container.innerHTML = userIngredients.map(ing => `
    <span class="tag">
        ${ing}
        <span class="tag-remove" onclick="removeIngredient('${ing}')" title="Remove">&times;</span>
    </span>
    `).join('');
}

    // ── Recipe matching ──
    function findRecipes() {
      if (userIngredients.length === 0) {
        alert('Please add at least one ingredient first!');
        return;
      }

      const resultsSection = document.getElementById('results-section');
      const loadingState   = document.getElementById('loading-state');
      const resultsGrid    = document.getElementById('results-grid');
      const noResults      = document.getElementById('no-results');

      resultsSection.classList.remove('hidden');
      loadingState.classList.remove('hidden');
      resultsGrid.innerHTML = '';
      noResults.classList.add('hidden');
      resultsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

      // Simulate async fetch from recipe.json
      setTimeout(() => {
        loadingState.classList.add('hidden');

        const matches = RECIPES
          .map(recipe => {
            const have    = recipe.ingredients.filter(i => userIngredients.includes(i));
            const missing = recipe.ingredients.filter(i => !userIngredients.includes(i));
            const score   = have.length / recipe.ingredients.length;
            return { ...recipe, have, missing, score };
          })
          .filter(r => r.score > 0)
          .sort((a, b) => b.score - a.score);

        document.getElementById('results-heading').textContent =
          matches.length > 0
            ? `${matches.length} recipe${matches.length !== 1 ? 's' : ''} found for you`
            : 'No recipes found';

        if (matches.length === 0) {
          noResults.classList.remove('hidden');
          return;
        }

        resultsGrid.innerHTML = matches.map((recipe, i) => buildRecipeCard(recipe, i)).join('');
      }, 800);
    }

    function buildRecipeCard(recipe, index) {
      const pct   = Math.round(recipe.score * 100);
      const delay = index * 80;

      const haveItems = recipe.have.map(i =>
        `<li><span class="dot dot-have"></span> ${capitalize(i)} <span style="color:#3AAD6E;font-size:0.75rem;">✓</span></li>`
      ).join('');

      const missingItems = recipe.missing.map(i =>
        `<li><span class="dot dot-missing"></span> ${capitalize(i)}</li>`
      ).join('');

      const storeHtml = buildStoreInfo(recipe.missing);

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
            ${recipe.missing.length > 0 ? `
              <div class="stores-section">
                <h4>Where to get the rest</h4>
                ${storeHtml}
              </div>
            ` : `<p style="font-size:0.85rem;color:#3AAD6E;font-weight:600;">You have everything!</p>`}
          </div>
        </div>
      `;
    }

function buildStoreInfo(missingItems) {
    if (missingItems.length === 0) return '';

    return Object.entries(STORE_INVENTORY)
    .map(([store, inventory]) => {
        const available = missingItems.filter(i => inventory.includes(i));
        if (available.length === 0) return '';
        return `
        <div class="store-item">
            <span class="store-name">${store}</span>
            <span class="store-items-count">${available.length} of ${missingItems.length} missing items</span>
        </div>
        `;
    })
    .filter(Boolean)
    .join('');
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