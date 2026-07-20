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
 * Joomdle login landing script
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing
require('../../config.php');
// phpcs:enable moodle.Files.RequireLogin.Missing
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/auth/joomdle/auth.php');

/**
 * Checks whether an absolute URL belongs to the same origin as a configured base URL.
 *
 * @param string $url URL to validate.
 * @param string $baseurl Allowed base URL.
 * @return bool
 */
function auth_joomdle_same_origin($url, $baseurl) {
    $urlparts = parse_url($url);
    $baseparts = parse_url($baseurl);

    if (
        empty($urlparts['scheme']) || empty($urlparts['host']) ||
        empty($baseparts['scheme']) || empty($baseparts['host'])
    ) {
        return false;
    }

    $urlscheme = strtolower($urlparts['scheme']);
    $basescheme = strtolower($baseparts['scheme']);
    $urlhost = strtolower($urlparts['host']);
    $basehost = strtolower($baseparts['host']);
    $urlport = $urlparts['port'] ?? (($urlscheme === 'https') ? 443 : 80);
    $baseport = $baseparts['port'] ?? (($basescheme === 'https') ? 443 : 80);

    return $urlscheme === $basescheme && $urlhost === $basehost && $urlport === $baseport;
}

/**
 * Normalises wantsurl so it can only point to Joomla or Moodle.
 *
 * Relative URLs are interpreted as Joomla pages.
 *
 * @param string $wantsurl Incoming wantsurl parameter.
 * @return string Normalised absolute URL, or empty string when invalid.
 */
function auth_joomdle_clean_wantsurl($wantsurl) {
    global $CFG;

    $wantsurl = trim($wantsurl);
    if ($wantsurl === '' || preg_match('#^//#', $wantsurl)) {
        return '';
    }

    $wantsurl = clean_param($wantsurl, PARAM_URL);
    if ($wantsurl === '') {
        return '';
    }

    $joomlaurl = get_config('auth_joomdle', 'joomla_url');
    if (!$joomlaurl) {
        return '';
    }

    if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $wantsurl)) {
        $joomlaparts = parse_url($joomlaurl);
        if (empty($joomlaparts['scheme']) || empty($joomlaparts['host'])) {
            return '';
        }

        $joomlaorigin = $joomlaparts['scheme'] . '://' . $joomlaparts['host'];
        if (!empty($joomlaparts['port'])) {
            $joomlaorigin .= ':' . $joomlaparts['port'];
        }

        if ($wantsurl[0] !== '/') {
            $joomlapath = empty($joomlaparts['path']) ? '' : rtrim($joomlaparts['path'], '/');
            $wantsurl = $joomlapath . '/' . $wantsurl;
        }

        $wantsurl = $joomlaorigin . $wantsurl;
    }

    if (
        auth_joomdle_same_origin($wantsurl, $joomlaurl) ||
        auth_joomdle_same_origin($wantsurl, $CFG->wwwroot)
    ) {
        return $wantsurl;
    }

    return '';
}

// It gives a warning if no context set, I guess it does nor matter which we use.
$PAGE->set_context(context_system::instance());

// Grab the GET params.
$token         = optional_param('token', '', PARAM_TEXT);
$username = optional_param('username', '', PARAM_TEXT);
$username = strtolower($username);
$createuser = optional_param('create_user', '', PARAM_TEXT);
$wantsurl      = optional_param('wantsurl', '', PARAM_RAW_TRIMMED);
$wantsurl = auth_joomdle_clean_wantsurl($wantsurl);
$usewrapper      = optional_param('use_wrapper', '', PARAM_TEXT);
$id      = optional_param('id', '', PARAM_INT);
$courseid      = optional_param('course_id', '', PARAM_INT); // Additional course_id param used for quiz view.
$mtype      = optional_param('mtype', '', PARAM_TEXT);
$day      = optional_param('day', '', PARAM_TEXT);
$mon      = optional_param('mon', '', PARAM_TEXT);
$year      = optional_param('year', '', PARAM_TEXT);
$time      = optional_param('time', '', PARAM_TEXT);
$itemid      = optional_param('Itemid', '', PARAM_TEXT);
$lang      = optional_param('lang', '', PARAM_TEXT);
$topic      = optional_param('topic', '', PARAM_INT);
$section      = optional_param('section', '', PARAM_INT);
$redirect      = optional_param('redirect', '', PARAM_TEXT); // Redirect moodle param.

$auth = new auth_plugin_joomdle();

$overrideitemid = $auth->call_method('getJoomdleDefaultItemid');

if ($overrideitemid) {
    $itemid = $overrideitemid;
}

// First check this is a Joomdle user.
$user = get_complete_user_data('username', $username);
if ((!$user) || ($user->auth == 'joomdle')) {
    if (($username != 'guest') && ((!isloggedin()) || (isguestuser()))) {
        /* Logged user trying to access */
        $logged = $auth->call_method("confirmJoomlaSession", [
            'username' => $username,
            'joomdle_auth_token' => $token,
        ]);

        if ($logged === true) {
            // User is logged in Joomla.
            $user = get_complete_user_data('username', $username);
            if (!$user) {
                if ($createuser) {
                    $auth->create_joomdle_user($username);
                } else {
                    /* If the user does not exists and we don't have to create it, we are done */
                    $redirecturl = get_config('auth_joomdle', 'joomla_url');
                    redirect($redirecturl);
                }
            }
            $user = get_complete_user_data('username', $username);

            if (!$user->suspended) {
                // Log the user in.
                complete_user_login($user);

                // Call user_authenticated_hook.
                $authsenabled = get_enabled_auth_plugins();
                foreach ($authsenabled as $hau) {
                    $hauth = get_auth_plugin($hau);
                    $hauth->user_authenticated_hook($user, $username, "");
                }
            }
        } // Logged.
    } // Username != guest.
} // auth = joomdle

// Redirect.
if ($usewrapper) {
    $redirecturl = get_config('auth_joomdle', 'joomla_url');
    switch ($mtype) {
        case "event":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                "&time=$time&Itemid=$itemid";
            break;
        case "course":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            if ($topic) {
                $redirecturl .= '&topic=' . $topic;
            }
            if ($section) {
                $redirecturl .= '&section=' . $section;
            }
            break;
        case "coursecategory":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "news":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "forum":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                "&course_id=$courseid&Itemid=$itemid";
            break;
        case "user":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "edituser":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
            break;
        case "resource":
        case "quiz":
        case "page":
        case "assignment":
        case "folder":
            $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                "&course_id=$courseid&Itemid=$itemid";
            break;
        default:
            if ($mtype) {
                $redirecturl .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id" .
                    "&course_id=$courseid&Itemid=$itemid";
            } else {
                if ($wantsurl) {
                    $redirecturl = $wantsurl;
                } else {
                    $redirecturl = get_config('auth_joomdle', 'joomla_url');
                }
            }
    }
    if ($redirect) {
        $redirecturl .= "&redirect=1";
    }
} else {
    $redirecturl = $CFG->wwwroot;
    switch ($mtype) {
        case "course":
            $redirecturl .= "/course/view.php?id=$id";

            if ($topic) {
                $redirecturl .= '&topic=' . $topic;
            }
            if ($section) {
                $redirecturl .= '#section-' . $section;
            }
            break;
        case "coursecategory":
            $redirecturl .= "/course/index.php?categoryid=" . $id;
            break;
        case "news":
            $redirecturl .= "/mod/forum/discuss.php?d=$id";
            break;
        case "forum":
            $redirecturl .= "/mod/forum/view.php?id=$id";
            break;
        case "event":
            $redirecturl .= "/calendar/view.php?view=day&time=$time";
            break;
        case "user":
            $redirecturl .= "/user/view.php?id=$id";
            break;
        case "resource":
            $redirecturl .= "/mod/resource/view.php?id=$id";
            break;
        case "quiz":
            $redirecturl .= "/mod/quiz/view.php?id=$id";
            break;
        case "page":
            $redirecturl .= "/mod/page/view.php?id=$id";
            break;
        case "assignment":
            $redirecturl .= "/mod/assignment/view.php?id=$id";
            break;
        case "folder":
            $redirecturl .= "/mod/folder/view.php?id=$id";
            break;
        default:
            if ($mtype) {
                $redirecturl .= "/mod/$mtype/view.php?id=$id";
            } else {
                if ($wantsurl) {
                    $redirecturl = $wantsurl;
                } else {
                    $redirecturl = get_config('auth_joomdle', 'joomla_url');
                }
            }
    }
    if ($redirect) {
        $redirecturl .= "&redirect=1";
    }
}

if ($lang) {
    $redirecturl .= '&lang=' . $lang;
}

// Kludge to deal with login form with no redirect set.
if (strstr($redirecturl, 'task=user.login')) {
    $redirecturl = get_config('auth_joomdle', 'joomla_url');
}

redirect($redirecturl);
