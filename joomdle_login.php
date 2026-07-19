<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Joomdle alternative login form
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing
require_once('../../config.php');
// phpcs:enable moodle.Files.RequireLogin.Missing
$login = optional_param('login', '', PARAM_TEXT);

$logintoken = \core\session\manager::get_login_token();
// Normal login to Moodle.
if ($login == 'moodle') {
    $actionurl = $CFG->wwwroot . '/login/index.php';

    echo <<<HTML
<html>
<head>
<title>Joomdle - Moodle login</title>
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<h3>Joomdle - Moodle Login</h3>
<form action="$actionurl" method="post">
<input type="hidden" name="logintoken" value="$logintoken">
Username: <input type="text" name="username">
<br>
Password: <input type="password" name="password">
<br>
<input type="submit" value="Login">
</form>
</body>
</html>
HTML;
} else {
    // Redirect to Joomla.
    $url = get_config('auth_joomdle', 'joomla_url');
    header("Location: $url");
}
