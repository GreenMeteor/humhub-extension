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

// Remove database user
$sqlDropUser = "DROP USER IF EXISTS 'humhub_user'@'localhost'";
if (!mysqli_query($conn, $sqlDropUser)) {
    die("Error dropping user: " . mysqli_error($conn) . "<br>");
} else {
    echo "User dropped successfully<br>";
}

// Remove database
$sqlDropDb = "DROP DATABASE IF EXISTS humhub_db";
if (!mysqli_query($conn, $sqlDropDb)) {
    die("Error dropping database: " . mysqli_error($conn) . "<br>");
} else {
    echo "Database dropped successfully<br>";
}

// Close MySQL connection
mysqli_close($conn);

// Remove HumHub files and directories
$humhubDir = __DIR__;

// Function to recursively delete a directory
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

if (is_dir($humhubDir)) {
    deleteDirectory($humhubDir);
    echo "HumHub files and directories removed successfully<br>";
} else {
    echo "HumHub directory not found<br>";
}

?>
