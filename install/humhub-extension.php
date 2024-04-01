<?php

class HumHubExtension
{
    private $config;

    public function __construct()
    {
        // Load configuration from config.json
        $configFile = 'config.json';
        if (file_exists($configFile)) {
            $configJson = file_get_contents($configFile);
            $this->config = json_decode($configJson, true);
        } else {
            throw new Exception("Config file not found: $configFile");
        }
    }

    public function install()
    {
        try {
            $this->log("Starting installation...");

            // Installation logic
            $this->downloadHumHub();
            $this->extractHumHub();
            $this->setPermissions();
            $this->createSymlink();
            $this->createDatabase();

            $this->log("HumHub installed successfully.");

            // Open a new tab to the HumHub installation URL
            echo "<script>window.open('http://{$this->config['domain']}', '_blank');</script>";
        } catch (Exception $e) {
            $this->log("Installation failed: " . $e->getMessage());
        }
    }

    public function uninstall()
    {
        try {
            $this->log("Starting uninstallation...");

            // Uninstallation logic
            $this->removeSymlink();
            $this->deleteDatabase();

            $this->log("HumHub uninstalled successfully.");
        } catch (Exception $e) {
            $this->log("Uninstallation failed: " . $e->getMessage());
        }
    }

    private function downloadHumHub()
    {
        $this->log("Downloading HumHub...");
        // Download HumHub
        $humhubVersion = '1.15.4'; // Specify the desired version of HumHub
        $humhubUrl = "https://download.humhub.com/downloads/install/humhub-$humhubVersion.zip";
        $zipFilePath = '/tmp/humhub.zip';
        file_put_contents($zipFilePath, fopen($humhubUrl, 'r'));
    }

    private function extractHumHub()
    {
        $this->log("Extracting HumHub...");
        // Extract HumHub
        $zip = new ZipArchive;
        $zipFilePath = '/tmp/humhub.zip';
        if ($zip->open($zipFilePath) === TRUE) {
            $zip->extractTo($this->config['installationPath']);
            $zip->close();
        } else {
            throw new Exception("Failed to extract HumHub.");
        }
    }

    private function setPermissions()
    {
        $this->log("Setting permissions...");
        // Set correct permissions
        chmod($this->config['installationPath'], 0755);
    }

    private function createSymlink()
    {
        $this->log("Creating symlink...");
        // Create a symbolic link for Apache
        exec("ln -s {$this->config['installationPath']} /var/www/html/humhub");
    }

    private function removeSymlink()
    {
        $this->log("Removing symlink...");
        // Remove the symbolic link
        exec("rm -rf /var/www/html/humhub");
    }

    private function createDatabase()
    {
        $this->log("Creating database and user...");
        // Create MySQL database and user
        $dbConnection = new mysqli('localhost', 'root', 'root_password');
        if ($dbConnection->connect_error) {
            throw new Exception('Database connection failed: ' . $dbConnection->connect_error);
        }

        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS {$this->config['dbName']}";
        if ($dbConnection->query($sql) === TRUE) {
            // Create user and grant privileges
            $sql = "CREATE USER IF NOT EXISTS '{$this->config['dbUser']}'@'localhost' IDENTIFIED BY '{$this->config['dbPass']}'";
            $sql .= ";GRANT ALL PRIVILEGES ON {$this->config['dbName']}.* TO '{$this->config['dbUser']}'@'localhost'";
            if ($dbConnection->multi_query($sql) === TRUE) {
                $this->log("Database created and user privileges granted successfully.");
            } else {
                throw new Exception("Error creating user and granting privileges: " . $dbConnection->error);
            }
        } else {
            throw new Exception("Error creating database: " . $dbConnection->error);
        }

        $dbConnection->close();
    }

    private function deleteDatabase()
    {
        $this->log("Deleting database and user...");
        // Delete MySQL database and user
        $dbConnection = new mysqli('localhost', 'root', 'root_password');
        if ($dbConnection->connect_error) {
            throw new Exception('Database connection failed: ' . $dbConnection->connect_error);
        }

        // Drop database
        $sql = "DROP DATABASE IF EXISTS {$this->config['dbName']}";
        if ($dbConnection->query($sql) === TRUE) {
            // Drop user
            $sql = "DROP USER IF EXISTS '{$this->config['dbUser']}'@'localhost'";
            if ($dbConnection->query($sql) === TRUE) {
                $this->log("Database and user deleted successfully.");
            } else {
                throw new Exception("Error deleting user: " . $dbConnection->error);
            }
        } else {
            throw new Exception("Error deleting database: " . $dbConnection->error);
        }

        $dbConnection->close();
    }

    private function log($message)
    {
        // Log messages to a file
        $logDir = '/var/log/';
        $logFile = $logDir . 'humhub-extension.log';

        // Create log directory if it doesn't exist
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Create log file if it doesn't exist
        if (!file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        // Append message to log file
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
    }
}

// Instantiate the HumHubExtension class
$humHubExtension = new HumHubExtension();

// Check command-line arguments
if ($argc < 2) {
    echo "Usage: php humhub-extension.php [install|uninstall]" . PHP_EOL;
    exit(1);
}

// Determine action based on command-line argument
$action = $argv[1];
switch ($action) {
    case 'install':
        $humHubExtension->install();
        break;
    case 'uninstall':
        $humHubExtension->uninstall();
        break;
    default:
        echo "Invalid action. Usage: php humhub-extension.php [install|uninstall]" . PHP_EOL;
        exit(1);
}

?>
