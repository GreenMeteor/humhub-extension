<?php
// This script performs pre-uninstallation tasks for the HumHub Installer extension

// Read database configurations from config.json
$config = json_decode(file_get_contents('config.json'), true);

// Database configuration
$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];

// Connect to MySQL
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Remove database and user
$sqlDropUser = "DROP USER IF EXISTS 'humhub_user'@'localhost'";
if (mysqli_query($conn, $sqlDropUser)) {
    echo "User dropped successfully<br>";
} else {
    echo "Error dropping user: " . mysqli_error($conn) . "<br>";
}

$sqlDropDb = "DROP DATABASE IF EXISTS humhub_db";
if (mysqli_query($conn, $sqlDropDb)) {
    echo "Database dropped successfully<br>";
} else {
    echo "Error dropping database: " . mysqli_error($conn) . "<br>";
}

// Close MySQL connection
mysqli_close($conn);
?>
