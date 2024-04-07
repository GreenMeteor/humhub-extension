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

// Define the URL of the HumHub zip file to download
$humhubZipUrl = 'https://download.humhub.com/downloads/install/humhub-1.16.0-beta.1.zip';

// Define the directory where HumHub should be extracted (root directory)
$humhubExtractDir = __DIR__; 

// Download and extract HumHub
if (downloadAndExtractHumHub($humhubZipUrl, $humhubExtractDir)) {
    echo "HumHub downloaded and extracted successfully<br>";
} else {
    echo "Error downloading or extracting HumHub<br>";
}

// Function to download and extract HumHub
function downloadAndExtractHumHub($url, $extractDir) {
    // Download the zip file
    $zipFile = file_get_contents($url);
    if ($zipFile === false) {
        return false;
    }

    // Save the zip file to a temporary location
    $tempFile = tempnam(sys_get_temp_dir(), 'humhub');
    file_put_contents($tempFile, $zipFile);

    // Extract the zip file
    $zip = new ZipArchive;
    if ($zip->open($tempFile) === true) {
        $zip->extractTo($extractDir);
        $zip->close();
        unlink($tempFile); // Clean up temporary zip file
        return true;
    } else {
        unlink($tempFile); // Clean up temporary zip file
        return false;
    }
}

?>
