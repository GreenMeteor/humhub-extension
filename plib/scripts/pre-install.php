<?php
// This script performs pre-installation tasks for the HumHub Installer extension

// Check PHP version
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    die('Error: HumHub requires PHP version 8.1.0 or later.');
}

// Check for required PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json'];
foreach ($requiredExtensions as $extension) {
    if (!extension_loaded($extension)) {
        die("Error: The $extension PHP extension is required for HumHub.");
    }
}

// Check for PHP settings
$requiredSettings = ['memory_limit' => '256M', 'post_max_size' => '64M', 'upload_max_filesize' => '64M'];
foreach ($requiredSettings as $setting => $value) {
    if (ini_get($setting) != $value) {
        die("Error: $setting must be set to $value in php.ini for HumHub to function properly.");
    }
}

echo "Pre-installation checks passed successfully. Starting installation process...";
