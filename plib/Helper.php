<?php

namespace PleskExt\HumhubInstaller;

class Helper
{
    public static function getFormHtml()
    {
        return '
        <form id="humhub-installer-form" method="POST" action="/modules/humhub-installer/install">
            <label for="domain">Domain:</label>
            <input type="text" name="domain" required />

            <label for="dbUser">Database User:</label>
            <input type="text" name="dbUser" required />

            <label for="dbPass">Database Password:</label>
            <input type="password" name="dbPass" required />

            <label for="dbName">Database Name:</label>
            <input type="text" name="dbName" required />

            <button type="submit">Install HumHub</button>
        </form>
        ';
    }
}
