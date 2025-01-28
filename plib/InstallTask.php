<?php

namespace PleskExt\HumhubInstaller;

use pm_ApiRpc;
use pm_ServerFileManager;
use pm_Domain;
use pm_Exception;

class InstallTask
{
    private $domain;
    private $dbUser;
    private $dbPass;
    private $dbName;

    public function __construct($domain, $dbUser, $dbPass, $dbName)
    {
        $this->domain = $domain;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->dbName = $dbName;
    }

    public function run()
    {
        try {
            // Create database, download, and configure HumHub
            $this->createDatabase();
            $this->downloadHumHub();
            $this->configureHumHub();

            return ['success' => true];
        } catch (pm_Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function createDatabase()
    {
        $dbManager = new pm_ApiRpc;
        $dbManager->call('database', 'addDb', [
            'name' => $this->dbName,
            'type' => 'mysql',
            'domain' => $this->domain,
            'dbuser' => [
                'name' => $this->dbUser,
                'password' => $this->dbPass,
            ],
        ]);
    }

    private function downloadHumHub()
    {
        $humhubUrl = 'https://download.humhub.com/downloads/install/humhub-1.17.0.zip';
        $domainObj = pm_Domain::getByName($this->domain);
        $installPath = $domainObj->getDocumentRoot();

        $fileManager = new pm_ServerFileManager;
        $fileManager->downloadFile($humhubUrl, "$installPath/humhub.zip");
        $fileManager->unpackArchive("$installPath/humhub.zip", $installPath);
        $fileManager->removeFile("$installPath/humhub.zip");
    }

    private function configureHumHub()
    {
        $domainObj = pm_Domain::getByName($this->domain);
        $configPath = $domainObj->getDocumentRoot() . '/protected/config/dynamic.php';

        $dynamicConfig = [
            'components' => [
                'db' => [
                    'class' => 'yii\\db\\Connection',
                    'dsn' => 'mysql:host=localhost;dbname=' . $this->dbName,
                    'username' => $this->dbUser,
                    'password' => $this->dbPass,
                ],
                'user' => [],
                'mailer' => [
                    'transport' => [
                        'dsn' => 'native://default',
                    ],
                ],
                'cache' => [
                    'class' => 'yii\\caching\\ApcCache',
                    'keyPrefix' => 'humhub',
                    'useApcu' => true,
                ],
            ],
            'params' => [
                'installer' => [
                    'db' => [
                        'installer_hostname' => 'localhost',
                        'installer_database' => $this->dbName,
                    ],
                ],
                'config_created_at' => time(),
                'horImageScrollOnMobile' => 1,
                'databaseInstalled' => true,
                'installed' => true,
            ],
            'name' => $this->domain,
        ];

        // Write the configuration file
        file_put_contents($configPath, "<?php return " . var_export($dynamicConfig, true) . "; ?>");
    }
}
