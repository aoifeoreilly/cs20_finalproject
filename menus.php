<?php 
include 'header.php'; 
include 'db_connect.php';

$today = date('Y-m-d');

function getMenu($db, $loc, $meal, $date) {
    $stmt = $db->prepare("SELECT item_name FROM menu_items WHERE location = ? AND meal_type = ? AND menu_date = ?");
    $stmt->bind_param("sss", $loc, $meal, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while($row = $result->fetch_assoc()) {
        $items[] = $row['item_name'];
    }
    $stmt->close();
    return $items;
}

$carmBreakfast = getMenu($dbConnection, 'carmichael-dining-hall', 'breakfast', $today);
$carmLunch     = getMenu($dbConnection, 'carmichael-dining-hall', 'lunch', $today);
$carmDinner    = getMenu($dbConnection, 'carmichael-dining-hall', 'dinner', $today);

$dewickBreakfast = getMenu($dbConnection, 'dewick-dining', 'breakfast', $today);
$dewickLunch     = getMenu($dbConnection, 'dewick-dining', 'lunch', $today);
$dewickDinner    = getMenu($dbConnection, 'dewick-dining', 'dinner', $today);

?>

<!-- Review Modal -->
<div id="review-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content builder-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 id="modal-title">Review Item</h3>
            <button onclick="closeModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <form action="food_reviews.php" method="POST">
            <input type="hidden" id="modal-item-name" name="reviewed_item">
            
            <div class="form-group">
                <label>Reviewing</label>
                <p id="display-item-name" style="font-weight:bold; color:var(--primary);"></p>
            </div>

            <div class="form-group">
                <label for="reviewer_name">Your Name</label>
                <input type="text" name="reviewer_name" placeholder="Name or Nickname" required>
            </div>

            <div class="form-group">
                <label for="rating">Rating</label>
                <select name="rating" required>
                    <option value="5">5 Stars - Excellent</option>
                    <option value="4">4 Stars - Very Good</option>
                    <option value="3">3 Stars - Good</option>
                    <option value="2">2 Stars - Okay</option>
                    <option value="1">1 Star - Poor</option>
                </select>
            </div>

            <div class="form-group">
                <label for="review_text">Review</label>
                <textarea name="review_text" placeholder="How was it?" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary find-btn">Submit Review</button>
        </form>
    </div>
</div>

<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <p class="section-label">Tufts University - Live Menus</p>
            <h1>What's cooking today?</h1>
            <p>Click any item to leave a review!</p>
        </div>
        
        <div class="builder-card" style="max-width: 400px;">
            <h3>Update Data</h3>
            <button id="sync-btn" class="btn btn-secondary" onclick="syncData()">Sync with Nutrislice</button>
            <p id="sync-status" style="font-size: 0.85rem; margin-top: 0.5rem; display: none;"></p>
        </div>
    </div>
</section>

<!-- Carmichael Section -->
<section class="section">
    <div class="section-inner">
        <div class="results-header">
            <h2>Carmichael Dining Hall</h2>
            <span class="match-badge">Res Quad</span>
        </div>
        <div class="results-grid">
            <?php renderMealBox('Breakfast', $carmBreakfast); ?>
            <?php renderMealBox('Lunch', $carmLunch); ?>
            <?php renderMealBox('Dinner', $carmDinner); ?>
        </div>
    </div>
</section>

<!-- Dewick Section -->
<section class="section">
    <div class="section-inner">
        <div class="results-header">
            <h2>Dewick-MacPhie</h2>
            <span class="match-badge">Medford</span>
        </div>
        <div class="results-grid">
            <?php renderMealBox('Breakfast', $dewickBreakfast); ?>
            <?php renderMealBox('Lunch', $dewickLunch); ?>
            <?php renderMealBox('Dinner', $dewickDinner); ?>
        </div>
    </div>
</section>

<?php
function renderMealBox($title, $items) {
    echo '<div class="recipe-card">';
    echo '<div class="recipe-card-header"><h3>'.$title.'</h3></div>';
    echo '<div class="recipe-card-body"><ul class="ingredient-list">';
    if(empty($items)) {
        echo "<li>No items found</li>";
    } else {
        foreach($items as $item) {
            $safeItem = htmlspecialchars($item);
            echo '<li><a href="javascript:void(0)" class="menu-item-link" onclick="openReviewModal(\''.$safeItem.'\')">';
            echo '<span class="dot dot-have"></span> '.$safeItem.'</a></li>';
        }
    }
    echo '</ul></div></div>';
}
?>

<script>
// Open the review window for an item.
function openReviewModal(itemName) {
    document.getElementById('modal-item-name').value = itemName;
    document.getElementById('display-item-name').textContent = itemName;
    document.getElementById('review-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('review-modal').style.display = 'none';
}

function syncData() {
    const btn = document.getElementById('sync-btn');
    const status = document.getElementById('sync-status');
    btn.disabled = true;
    status.style.display = 'block';
    status.textContent = 'Syncing...';

    fetch('sync_menus.php')
        .then(response => response.text())
        .then(data => {
            status.textContent = '✓ Updated!';
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            btn.disabled = false;
            status.textContent = 'Error syncing.';
        });
}
</script>

<?php 
$dbConnection->close();
include 'footer.php'; 
?>