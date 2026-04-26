<?php
include 'db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create our sequel table for first call of this.
$dbConnection->query("
    CREATE TABLE IF NOT EXISTS menu_items (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        location    VARCHAR(100),
        meal_type   VARCHAR(50),
        item_name   VARCHAR(255),
        menu_date   DATE,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

//Create our uniwur index. 
$indexCheck = $dbConnection->query("SHOW INDEX FROM menu_items WHERE Key_name = 'idx_unique_menu'");
if ($indexCheck->num_rows == 0) {
    $dbConnection->query("CREATE UNIQUE INDEX idx_unique_menu ON menu_items (location, meal_type, item_name, menu_date)");
}

$locations = ["dewick-dining", "carmichael-dining-hall"];
$meals = ["breakfast", "lunch", "dinner"];

$year = date('Y');
$month = (int)date('m');
$day = (int)date('d');

foreach ($locations as $loc) {
    foreach ($meals as $meal) {
        // https://www.reddit.com/r/code/comments/1694zkj/api_help/ API Help
        $url = "https://tufts.api.nutrislice.com/menu/api/weeks/school/$loc/menu-type/$meal/$year/$month/$day/";
        // https://www.php.net/manual/en/book.curl.php Curl Help
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Check to make sure our request has succeeded.
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['days'])) {
                // Using insert ifgnore so we can avoid duplicate entires, ts is kinda large. 
                $stmt = $dbConnection->prepare("INSERT IGNORE INTO menu_items (location, meal_type, item_name, menu_date) VALUES (?, ?, ?, ?)");
                // Parse the data for that day out of our json
                foreach ($data['days'] as $dayData) {
                    $date = $dayData['date'];
                    if (isset($dayData['menu_items'])) {
                        foreach ($dayData['menu_items'] as $item) {
                            if (isset($item['food']['name'])) {
                                $itemName = $item['food']['name'];
                                $stmt->bind_param("ssss", $loc, $meal, $itemName, $date);
                                $stmt->execute();
                            }
                        }
                    }
                }
                $stmt->close();
            }
        }
    }
}
$dbConnection->close();
?>