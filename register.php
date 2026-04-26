<?php
include 'db_connect.php';

$error = '';

//if form subbmitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //saves all form values
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    $sub_type = $_POST['subscription_type'] ?? 'free';

    //ensure that username and password fields arent empty
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    
    } elseif (strlen($username) > 20) {                                                                                                                                                                                   
      $error = 'Username must be 20 characters or fewer.'; //make sure username isnt super long                                                                                                                                                     
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.'; //ensure that passowrd and confirmation match
    } else {
        $stmt = $dbConnection->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Username already taken.';
        } else {
            //connect to db and add user
            $stmt->close();
            $stmt = $dbConnection->prepare("INSERT INTO users (username, password, subscription_type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password, $sub_type);
            $stmt->execute();
            $stmt->close();
            $dbConnection->close();
            //riderect to login page
            header("Location: login.php?registered=1");
            exit();
        }
        $stmt->close();
    }
    $dbConnection->close();
}

include 'header.php';
?>

<section class="hero" style="min-height: 70vh; display:flex; align-items:center;">
    <div class="hero-inner" style="display:flex; justify-content:center; width:100%;">
        <div class="builder-card" style="width: 100%; max-width: 520px; padding: 3rem;">
            <h3>Create Account</h3>

            <?php if (!empty($error)): ?>
                <!-- print error message and sanitize before to avoid XSS - shoutout Ming (ignore the fact that passowrds are unencrypted - sorry Ming) -->
                <p style="color:#ef4444; font-size:0.9rem; margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></p> 
            <?php endif; ?>

            <!-- post so data is not visible in URL -->
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <!-- password type so characters are masekd -->
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label>Account Type</label>
                    <select name="subscription_type">
                        <option value="free">Free</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary find-btn">Create Account</button>
            </form>

            <p style="text-align:center; margin-top:1.5rem; font-size:0.9rem; color:#6b7280;">
                Already have an account? <a href="login.php" style="color:var(--primary); font-weight:600;">Login here</a>
            </p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
