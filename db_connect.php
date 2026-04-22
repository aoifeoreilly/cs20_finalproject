<?php
/* ── Database credentials ── */
$dbHost     = "localhost";
$dbUser     = "uoh0npdlssfzz";
$dbPassword = "iLoveCS20";
$dbName     = "db9hbhvhbnvreb";

// Establish connection
$dbConnection = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

if ($dbConnection->connect_error) {
    die("Connection failed: " . $dbConnection->connect_error);
}
?>