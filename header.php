  <?php
  //only starts session if one isn't running                                                                                                                                                                                                           
  if (session_status() === PHP_SESSION_NONE) {                                                                                                                                                                    
      session_start();                                                                                                                                                                                            
  }               
  ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>TuftsEats</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css"/>
    <link rel="stylesheet" type="text/css" href="home.css"/>
    <link rel="stylesheet" type="text/css" href="process_ingredients.css"/>
</head>
<body>

<!-- Navigation -->
<nav>
    <div class="nav-inner">
    <a class="nav-logo" href="home.php">Tufts<span>Eats</span></a>
    <ul class="nav-links">
        <li><a href="home.php">Recipe Builder</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="food_reviews.php">Reviews</a></li>
        <li><a href="menus.php">Tufts Menus</a></li>
        <?php 
        //check if user is signed in to determine whether we show username or login prompt
            if(isset( $_SESSION['user_id'])): ?>
                <!-- hide mobile tag since it is not neccesary to have this on mobile-->
                <li class="hide-mobile"><span style="color:#ffffffeb; font-size:15px; font-weight:600; padding:10px 14px; display:inline-block;">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span></li>         
                <li><a href="logout.php">Logout</a></li> 
            <?php else: ?>
                <li><a href="login.php">Login</a><li>
            <?php endif; ?> 
    </ul>
    </div>
</nav>