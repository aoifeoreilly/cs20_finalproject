<?php
session_start();
include 'db_connect.php';

//only process form if it was submitted and user is logged in 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    //upgrade user to premium 
    $stmt = $dbConnection->prepare("UPDATE users SET subscription_type = 'premium' WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    $dbConnection->close();
    //ensure we update session as well
    $_SESSION['subscription_type'] = 'premium';
    //redirect
    header("Location: menus.php");
    exit();
}

include 'header.php';
?>

<section class="hero" style="min-height:70vh; display:flex; align-items:center;">
    <div class="hero-inner" style="display:flex; justify-content:center; width:100%;">
        <div class="builder-card" style="width:100%; max-width:520px; padding:3rem; text-align:center;">
            <h3 style="margin-bottom:0.5rem;">Upgrade to Premium</h3>
            <p class="section-label" style="margin-bottom:1.5rem;">$1.99 / month</p>

            <!-- using same styling as ingredient list -->
            <ul class="ingredient-list" style="text-align:left; margin-bottom:2rem;">
                <li><span class="dot dot-have"></span> Access to live Tufts dining menus</li>
                <li><span class="dot dot-have"></span> Real-time menu updates</li>
                <li><span class="dot dot-have"></span> Star ratings on every menu item</li>
                <li><span class="dot dot-have"></span> Support TuftsEats development</li>
            </ul>
            <form method="POST">
                <button type="submit" class="btn btn-primary find-btn">Upgrade Now</button>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
