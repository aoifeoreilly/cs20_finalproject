<?php include 'header.php'; ?>

<!-- Hero -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <p class="section-label">Tufts University - Recipe Builder</p>
            <h1>Turn what you have into something delicious!</h1>
            <p>
            Enter the ingredients you already have and TuftsEats will generate
            recipes you can make and which nearby stores carry any ingredients 
            that you're missing.
            </p>
        </div>

        <!-- Recipe Builder Form -->
        <div class="builder-card">
            <h3>Build Your Recipe</h3>
            <p class="subtitle">Add the ingredients you have on hand</p>

            <div class="ingredient-input-row">
            <input type="text" id="ingredient-input" placeholder="e.g. chicken"/>
            <button class="btn btn-secondary" onclick="addIngredient()">Add</button>
            </div>

            <div id="tag-container" aria-label="Added ingredients"></div>

            <button class="btn btn-primary find-btn" onclick="findRecipes()">
                Find Recipes
            </button>
        </div>

        </div>
    </section>

    <!-- Results -->
    <section id="results-section" class="hidden">
        <div class="results-inner">
        <div class="results-header">
            <div>
            <p class="section-label">Results</p>
            <h2 id="results-heading">Recipes for you</h2>
            </div>
            <button class="btn btn-outline" onclick="clearResults()">← Start Over</button>
        </div>

        <div id="loading-state" class="hidden">
            <p class="text-center" style="color:var(--text-light); margin-top:0.5rem;">Finding your recipes…</p>
        </div>

        <div id="results-grid" class="results-grid"></div>

        <div id="no-results" class="empty-state hidden">
            <h3>No matches found</h3>
        </div>
        </div>
    </section>

    <!-- How it works section -->
    <section class="section how-section">
        <div class="section-inner">
        <div class="text-center">
            <p class="section-label">How It Works</p>
            <h2>Three steps to your next meal</h2>
            <div class="divider" style="margin: 1rem auto 0;"></div>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon">1</div>
                    <h4>Enter Your Ingredients</h4>
                    <p>Tell us what's already in your fridge, pantry, or dining hall haul. Add as many ingredients as you like.</p>
                </div>
            <div class="step-card">
                <div class="step-icon">2</div>
                    <h4>Get Matched Recipes</h4>
                    <p>We match your ingredients against our recipe database and surface the best options ranked by how much you already have.</p>
                </div>
            <div class="step-card">
                <div class="step-icon">3</div>
                    <h4>Find Missing Items</h4>
                    <p>For any ingredients you're short on, we show which nearby store carries them.</p>
            </div>
        </div>
        </div>
    </section>

    <script src="process_ingredients.js"></script>

<?php include 'footer.php'; ?>