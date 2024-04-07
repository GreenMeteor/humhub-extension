<?php
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

// Create database and user
$sqlCreateDb = "CREATE DATABASE IF NOT EXISTS humhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($conn, $sqlCreateDb)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Create database user and grant privileges
$sqlCreateUser = "CREATE USER 'humhub_user'@'localhost' IDENTIFIED BY 'user_password'";
if (mysqli_query($conn, $sqlCreateUser)) {
    echo "User created successfully<br>";
} else {
    echo "Error creating user: " . mysqli_error($conn) . "<br>";
}

// Grant privileges to the user for the database
$sqlGrantPrivileges = "GRANT ALL PRIVILEGES ON humhub_db.* TO 'humhub_user'@'localhost'";
if (mysqli_query($conn, $sqlGrantPrivileges)) {
    echo "Privileges granted successfully<br>";
} else {
    echo "Error granting privileges: " . mysqli_error($conn) . "<br>";
}

// Close MySQL connection
mysqli_close($conn);
?>
