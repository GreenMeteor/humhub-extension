<?php

// Load the Plesk module initialization file
require_once('/usr/local/psa/admin/plib/init.php');

// Include your extension's controller file
require_once('/usr/local/psa/admin/plib/modules/humhub-extension/controllers/IndexController.php');

// Create an instance of the extension's controller and handle the request
$controller = new \humhub\extension\controllers\IndexController();
$controller->indexAction();

?>
