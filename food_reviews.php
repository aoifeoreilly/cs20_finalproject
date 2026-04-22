<?php
include 'db_connect.php';

// Ensure the table and column exist
$dbConnection->query("CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_name VARCHAR(120),
    rating INT,
    review_text TEXT,
    reviewed_item VARCHAR(255) DEFAULT 'General',
    created_at DATETIME
)");

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['reviewer_name'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $text = trim($_POST['review_text'] ?? '');
    $item = trim($_POST['reviewed_item'] ?? 'General');

    if (!empty($name) && !empty($text) && $rating > 0) {
        $stmt = $dbConnection->prepare("INSERT INTO reviews (reviewer_name, rating, review_text, reviewed_item, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("siss", $name, $rating, $text, $item);
        $stmt->execute();
        $stmt->close();
        header("Location: food_reviews.php?success=1");
        exit();
    }
}

include 'header.php';

// Handle Search Query
$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt = $dbConnection->prepare("SELECT * FROM reviews WHERE reviewed_item LIKE ? OR review_text LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $dbConnection->query("SELECT * FROM reviews ORDER BY created_at DESC");
}
?>

<section class="hero">
    <div class="hero-inner">
        <div class="hero-text">
            <p class="section-label">Tufts University · Review Database</p>
            <h1>Community Feedback</h1>
            <p>
                Search our database of student reviews for Tufts dining food.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="builder-card" style="margin-top: 2rem; max-width: 600px;">
            <form action="food_reviews.php" method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" placeholder="Search for a dish (e.g. Grilled Chicken)..." 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       style="flex-grow:1; padding:0.75rem; border:1px solid #ddd; border-radius:0.5rem;">
                <button type="submit" class="btn btn-primary" style="padding:0.75rem 1.5rem;">Search</button>
            </form>
            <?php if (!empty($search)): ?>
                <p style="margin-top:0.5rem; font-size:0.85rem; color:#6b7280;">
                    Showing results for "<strong><?php echo htmlspecialchars($search); ?></strong>" 
                    — <a href="food_reviews.php" style="color:var(--primary);">Clear search</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-inner">
        <?php if (isset($_GET['success'])): ?>
            <p class="alert-success">
                ✓ Your review has been successfully posted!
            </p>
        <?php endif; ?>

        <div class="results-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="recipe-card" style="margin-bottom: 1.5rem;">
                        <div class="recipe-card-header">
                            <h3 style="font-size: 1.1rem;"><?php echo htmlspecialchars($row['reviewed_item']); ?></h3>
                            <span class="match-badge"><?php echo (int)$row['rating']; ?> / 5</span>
                        </div>
                        <div class="recipe-card-body">
                            <p style="font-style: italic; color: #4b5563; margin-bottom: 0.75rem;">
                                "<?php echo nl2br(htmlspecialchars($row['review_text'])); ?>"
                            </p>
                            <div style="border-top: 1px solid #f3f4f6; padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.85rem; font-weight: 600; color: #2d5f9f;">
                                    By <?php echo htmlspecialchars($row['reviewer_name']); ?>
                                </span>
                                <span style="font-size: 0.8rem; color: #9ca3af;">
                                    <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: #f9fafb; border-radius: 1rem;">
                    <p style="color: #6b7280; font-size: 1.1rem;">No reviews found matching your search.</p>
                    <a href="food_reviews.php" class="btn btn-secondary" style="margin-top: 1rem; display: inline-block;">View All Reviews</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$dbConnection->close();
include 'footer.php';
?>