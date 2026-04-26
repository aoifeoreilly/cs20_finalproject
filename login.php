<?php 
    session_start();
    include 'db_connect.php';


    //POST check
    $error = '';
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        //get username and password from form
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

         //ensure that username and password fields arent empty
        if (empty($username) || empty($password)) {
            $error = 'Username and password are required.';
        }
        //ensure that username and passowrd exist in DB
        else{
        // https://www.php.net/manual/en/mysqli.prepare.php for using prepare
        $stmt = $dbConnection->prepare("SELECT id, username, subscription_type FROM users WHERE username = ? AND password = ?");
        // https://www.php.net/manual/en/mysqli-stmt.bind-param.php FOr using bind param
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        //check if a row exists
        if ($result->num_rows == 0) {
            $error = 'Incorrect username or password.';
        }else{
            //login succesful save details for session
            $row = $result->fetch_assoc();                                                                                                                                                                                  
            $_SESSION['user_id'] = $row['id'];                                                                                                                                                                              
            $_SESSION['username'] = $row['username'];                                                                                                                                                                       
            $_SESSION['subscription_type'] = $row['subscription_type'];  

            //close everything and redirect to home
            $stmt->close();
            //riderect to login page
            header("Location: home.php?login=1");
            exit();
            }
        }
    }
    
?> 

<?php
    include 'header.php';
?>

                  
  <section class="hero" style="min-height: 70vh; display:flex; align-items:center;">
      <div class="hero-inner" style="display:flex; justify-content:center; width:100%;">
          <div class="builder-card" style="width: 100%; max-width: 520px; padding: 3rem;">
            
            <h3>Login</h3>           
            <!-- for when coming form signup after creating an account -->
            <?php if (isset($_GET['registered'])): ?>                                                                                                                                                                       
                <p style="color:#3AAD6E; font-size:0.9rem; margin-bottom:1rem;">Account created! Please log in.</p>
            <?php endif; ?>   

            <!-- error message again -->
            <?php if (!empty($error)): ?>
                <p style="color:#ef4444; font-size:0.9rem; margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></p>                                                                                                 
            <?php endif; ?>                                                                                                                                                                             
              <form action="login.php" method="POST">
                  <div class="form-group">                                                                                                                                                                        
                      <label>Username</label>
                      <input type="text" name="username" required>                                                                                                                                                
                  </div>
                  <div class="form-group">                                                                                                                                                                        
                      <label>Password</label>
                      <input type="password" name="password" required>
                  </div>                                                                                                                                                                                          
                  <button type="submit" class="btn btn-primary find-btn">Login</button>
              </form>
              <p style="text-align:center; margin-top:1.5rem; font-size:0.9rem; color:#6b7280;">
                  Don't have an account? <a href="register.php" style="color:var(--primary); font-weight:600;">Sign up here</a>
              </p>
          </div>  
      </div>
  </section>

  <?php include 'footer.php'; ?>
