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
 * @copyright  2009 Qontori Pte Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$login = optional_param('login', '', PARAM_TEXT);

$login_token = \core\session\manager::get_login_token();
// Normal login to Moodle.
if ($login == 'moodle') {
?>
<html>
<head>
<title>Joomdle - Moodle login</title>
<meta name="robots" content="noindex, nofollow">
</head>
<body>
<h3>Joomdle - Moodle Login</h3>
<FORM action="<?php echo $CFG->wwwroot; ?>/login/index.php" method="POST">
<input type="hidden" name="logintoken" value="<?php echo $login_token; ?>">
Username: <input type=text name="username">
<br>
Password: <input type=password name="password">
<br>
<INPUT type="SUBMIT" value="Login">
</FORM>
</body>
</html>
<?php
} else {
    // Redirect to Joomla.
    $url = get_config('auth_joomdle', 'joomla_url');
    header ("Location: $url");
}
