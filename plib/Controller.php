<?php

namespace PleskExt\HumhubInstaller;

use PleskExt\HumhubInstaller\Helper;
use PleskExt\HumhubInstaller\InstallTask;

class Controller extends \pm_Controller
{
    public function indexAction()
    {
        $this->view->pageTitle = "HumHub Installer";
        $this->view->content = Helper::getFormHtml();
    }

    public function installAction()
    {
        $domain = $this->_request->getParam('domain');
        $dbUser = $this->_request->getParam('dbUser');
        $dbPass = $this->_request->getParam('dbPass');
        $dbName = $this->_request->getParam('dbName');

        $task = new InstallTask($domain, $dbUser, $dbPass, $dbName);
        $result = $task->run();

        if ($result['success']) {
            $this->_helper->json(['success' => true, 'message' => 'HumHub installation completed!']);
        } else {
            $this->_helper->json(['success' => false, 'message' => $result['message']]);
        }
    }
}
