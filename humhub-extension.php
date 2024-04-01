<?php

class HumHubExtension
{
    private $installationPath = '/var/www/humhub'; // Specify the installation directory
    private $dbName = 'humhub_prod_db'; // Database name
    private $dbUser = 'humhub_prod'; // Database user
    private $dbPass = 'change-me'; // Database password

    public function __construct()
    {
        // Constructor logic, if needed
    }

    public function install()
    {
        // Installation logic
        $this->downloadHumHub();
        $this->extractHumHub();
        $this->setPermissions();
        $this->createSymlink();
        $this->createDatabase();
        echo "HumHub installed successfully.";
    }

    public function uninstall()
    {
        // Uninstallation logic
        $this->removeSymlink();
        $this->deleteDatabase();
        echo "HumHub uninstalled successfully.";
    }

    private function downloadHumHub()
    {
        // Download HumHub
        $humhubVersion = '1.15.4'; // Specify the desired version of HumHub
        $humhubUrl = "https://download.humhub.com/downloads/install/humhub-$humhubVersion.zip";
        $zipFilePath = '/tmp/humhub.zip';
        file_put_contents($zipFilePath, fopen($humhubUrl, 'r'));
    }

    private function extractHumHub()
    {
        // Extract HumHub
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath) === TRUE) {
            $zip->extractTo($this->installationPath);
            $zip->close();
        }
    }

    private function setPermissions()
    {
        // Set correct permissions
        chmod($this->installationPath, 0755);
    }

    private function createSymlink()
    {
        // Create a symbolic link for Apache
        exec("ln -s $this->installationPath /var/www/html/humhub");
    }

    private function removeSymlink()
    {
        // Remove the symbolic link
        exec("rm -rf /var/www/html/humhub");
    }

    private function createDatabase()
    {
        // Create MySQL database and user
        $dbConnection = new mysqli('localhost', 'root', 'root_password');
        if ($dbConnection->connect_error) {
            die('Database connection failed: ' . $dbConnection->connect_error);
        }

        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS $this->dbName";
        if ($dbConnection->query($sql) === TRUE) {
            // Create user and grant privileges
            $sql = "CREATE USER IF NOT EXISTS '$this->dbUser'@'localhost' IDENTIFIED BY '$this->dbPass'";
            $sql .= ";GRANT ALL PRIVILEGES ON $this->dbName.* TO '$this->dbUser'@'localhost'";
            if ($dbConnection->multi_query($sql) === TRUE) {
                echo "Database created and user privileges granted successfully." . PHP_EOL;
            } else {
                echo "Error creating user and granting privileges: " . $dbConnection->error . PHP_EOL;
            }
        } else {
            echo "Error creating database: " . $dbConnection->error . PHP_EOL;
        }

        $dbConnection->close();
    }

    private function deleteDatabase()
    {
        // Delete MySQL database and user
        $dbConnection = new mysqli('localhost', 'root', 'root_password');
        if ($dbConnection->connect_error) {
            die('Database connection failed: ' . $dbConnection->connect_error);
        }

        // Drop database
        $sql = "DROP DATABASE IF EXISTS $this->dbName";
        if ($dbConnection->query($sql) === TRUE) {
            // Drop user
            $sql = "DROP USER IF EXISTS '$this->dbUser'@'localhost'";
            if ($dbConnection->query($sql) === TRUE) {
                echo "Database and user deleted successfully." . PHP_EOL;
            } else {
                echo "Error deleting user: " . $dbConnection->error . PHP_EOL;
            }
        } else {
            echo "Error deleting database: " . $dbConnection->error . PHP_EOL;
        }

        $dbConnection->close();
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
