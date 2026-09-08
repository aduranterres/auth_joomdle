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
 * Joomdle main class file
 *
 * Contains all Joomdle web service functions
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use gradereport_user\report\user as user_report;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/auth/manual/auth.php');
require_once($CFG->dirroot . '/auth/joomdle/lib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');
require_once($CFG->dirroot . '/lib/datalib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/lib/gdlib.php');
require_once($CFG->dirroot . '/lib/grade/grade_grade.php');
require_once($CFG->dirroot . '/lib/grade/grade_item.php');
require_once($CFG->dirroot . '/lib/gradelib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/lib/grouplib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/grade/report/user/lib.php');

/**
 * Joomdle authentication plugin.
 */
class auth_plugin_joomdle extends auth_plugin_manual {
    /** Teacher role identifier. */
    public const ROLE_TEACHER = 3;

    /** Student role identifier. */
    public const ROLE_STUDENT = 5;

    /** Maximum avatar download size (20 MiB). */
    private const AVATAR_MAX_SIZE = 20 * 1024 * 1024;

    /** Maximum avatar download time in seconds. */
    private const AVATAR_TIMEOUT = 10;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'joomdle';
        $this->config = get_config('auth_joomdle');
    }

    /**
     * Can signup.
     * @return mixed The result of the operation.
     */
    public function can_signup() {
        return true;
    }

    /**
     * User signup.
     *
     * @param mixed $user User.
     * @param mixed $notify Notify.
     * @return mixed The result of the operation.
     */
    public function user_signup($user, $notify = true) {
        global $CFG, $DB, $PAGE, $OUTPUT;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $passwordclear = $user->password;
        $user->password = hash_internal_user_password($user->password);

        if (! ($user->id = $DB->insert_record('user', $user))) {
            throw new \moodle_exception('auth_emailnoinsert', 'auth');
        }

        // Save any custom profile field information.
        profile_save_data($user);

        $conditions = ['id' => $user->id];
        $user = $DB->get_record('user', $conditions);

        /* Create user in Joomla */
        $userinfo = [];
        $userinfo['username'] = $user->username;
        $userinfo['password'] = $passwordclear;
        $userinfo['password2'] = $passwordclear;
        $userinfo['name'] = $user->firstname . " " . $user->lastname;
        $userinfo['firstname'] = $user->firstname;
        $userinfo['lastname'] = $user->lastname;
        $userinfo['email'] = $user->email;
        $userinfo['block'] = 0;
        $userinfo['confirmed'] = 0;

        // Manually create user in Joomla, because we only have the password in cleartext here.
        $this->call_method("createUser", ['userinfo' => $userinfo]);

        \core\event\user_updated::create_from_userid($user->id)->trigger();

        if (! send_confirmation_email($user)) {
            throw new \moodle_exception('auth_emailnoemail', 'auth');
        }

        if ($notify) {
            $emailconfirm = get_string('emailconfirm');
            $PAGE->set_url('/auth/joomdle/auth.php');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($emailconfirm);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', '', $user->email), "{$CFG->wwwroot}/index.php");
        } else {
            return true;
        }
    }

    /**
     * Can confirm.
     * @return mixed The result of the operation.
     */
    public function can_confirm() {
        return true;
    }

    /**
     * User confirm.
     *
     * @param mixed $username Username.
     * @param mixed $confirmsecret Confirmsecret.
     * @return mixed The result of the operation.
     */
    public function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else if ($user->auth != 'joomdle') {
                return AUTH_CONFIRM_ERROR;
            } else if ($user->secret == stripslashes($confirmsecret)) {   // They have provided the secret key to get in.
                $conditions = ['id' => $user->id];
                if (!$DB->set_field("user", "confirmed", 1, $conditions)) {
                    return AUTH_CONFIRM_FAIL;
                }
                if (!$DB->set_field("user", "firstaccess", time(), $conditions)) {
                    return AUTH_CONFIRM_FAIL;
                }

                /* Enable de user in Joomla */
                $this->call_method("activateUser", ['username' => $username]);

                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }


    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {

        if (!$password) {
            return false;
        }

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return false;
        }

        $logged = $this->call_method("login", [
            'username' => $username,
            'password' => $password,
        ]);

        return $logged;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    public function is_internal() {
        return true;
    }


    /**
     * Can change password.
     * @return mixed The result of the operation.
     */
    public function can_change_password() {
        return true;
    }

    /**
     * User update password.
     *
     * @param mixed $user User.
     * @param mixed $password Password.
     * @return mixed The result of the operation.
     */
    public function user_update_password($user, $password) {
        $this->call_method("changePassword", [
            'username' => $user->username,
            'password' => $password,
        ]);

        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $password);
    }

    /**
     * User update.
     *
     * @param mixed $olduser Olduser.
     * @param mixed $newuser Newuser.
     * @return mixed The result of the operation.
     */
    public function user_update($olduser, $newuser) {
        // Update username in Joomla if changed in Moodle.
        if ($olduser->username != $newuser->username) {
            $this->call_method("changeUsername", [
                'old_username' => $olduser->username,
                'new_username' => $newuser->username,
            ]);
        }
        return true;
    }

    /**
     *  get rest url.
     * @return mixed The result of the operation.
     */
    private function get_rest_url() {
        $joomlalang = get_config('auth_joomdle', 'joomla_lang');
        $joomlasef = get_config('auth_joomdle', 'joomla_sef');
        $joomlaauthtoken = get_config('auth_joomdle', 'joomla_auth_token');

        if ($joomlalang == '') {
            $joomlarestserverurl = get_config('auth_joomdle', 'joomla_url') .
                '/index.php?option=com_joomdle&task=ws.server&format=json';
        } else if ($joomlasef) {
            $joomlarestserverurl = get_config('auth_joomdle', 'joomla_url') .
            '/index.php/' . $joomlalang . '/?option=com_joomdle&task=ws.server&format=json';
        } else {
            $joomlarestserverurl = get_config('auth_joomdle', 'joomla_url') .
                '/index.php?lang=' . $joomlalang . '&option=com_joomdle&task=ws.server&format=json';
        }

        // Add auth token.
        $joomlarestserverurl .= "&token=" . $joomlaauthtoken;

        return $joomlarestserverurl;
    }

    /**
     * Call method.
     *
     * @param mixed $method Method.
     * @param mixed $params Params.
     * @param bool $diagnostic Whether to save the raw response for diagnostics.
     * @return mixed The result of the operation.
     */
    public function call_method($method, $params = [], $diagnostic = false) {
        $connectionmethod = get_config('auth_joomdle', 'connection_method');

        if ($connectionmethod == 'fgc') {
            return $this->call_method_fgc($method, $params, $diagnostic);
        }

        return $this->call_method_curl($method, $params, $diagnostic);
    }

    /**
     * Call method curl.
     *
     * @param mixed $method Method.
     * @param mixed $params Params.
     * @param bool $diagnostic Whether to save the raw response for diagnostics.
     * @return mixed The result of the operation.
     */
    private function call_method_curl($method, $params = [], $diagnostic = false) {
        $joomlaresturl = $this->get_rest_url();
        $url = $joomlaresturl . '&wsfunction=' . $method;

        $request = $this->format_postdata_for_curlcall($params);

        $curl = new curl();
        $curl->setHeader([
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . strlen($request),
            'User-Agent: Joomdle',
        ]);
        $response = $curl->post($url, $request);

        return $this->process_method_response($response, $diagnostic);
    }

    /**
     * Call method fgc.
     *
     * @param mixed $method Method.
     * @param mixed $params Params.
     * @param bool $diagnostic Whether to save the raw response for diagnostics.
     * @return mixed The result of the operation.
     */
    private function call_method_fgc($method, $params = [], $diagnostic = false) {
        $joomlaresturl = $this->get_rest_url();
        $url = $joomlaresturl . '&wsfunction=' . $method;

        $request = $this->format_postdata_for_curlcall($params);

        $context = stream_context_create(['http' => [
            'method' => "POST",
            'header' => "Content-Type: application/x-www-form-urlencoded\r\nUser-Agent: Joomdle",
            'content' => $request,
        ]]);
        $response = file_get_contents($url, false, $context);

        return $this->process_method_response($response, $diagnostic);
    }

    /**
     * Process a method response.
     *
     * @param string|false $response Raw response.
     * @param bool $diagnostic Whether to save the raw response for diagnostics.
     * @return mixed The result of the operation.
     */
    private function process_method_response($response, $diagnostic = false) {
        global $CFG;

        if ($diagnostic) {
            $tmpfile = $CFG->dataroot . '/temp/joomdle_system_check.json';
            file_put_contents($tmpfile, $response);
        }

        $response = trim($response);
        return json_decode($response, true);
    }

    /**
     * Format array postdata for curlcall.
     *
     * @param mixed $arraydata Arraydata.
     * @param mixed $currentdata Currentdata.
     * @param mixed $data Data.
     * @return mixed The result of the operation.
     */
    private function format_array_postdata_for_curlcall($arraydata, $currentdata, &$data) {
        foreach ($arraydata as $k => $v) {
            $newcurrentdata = $currentdata;
            if (is_object($v)) {
                $v = (array) $v;
            }
            if (is_array($v)) { // The value is an array, call the function recursively.
                $newcurrentdata = $newcurrentdata . '[' . urlencode($k) . ']';
                $this->format_array_postdata_for_curlcall($v, $newcurrentdata, $data);
            } else { // Add the POST parameter to the $data array.
                $k = $k ? $k : '';
                $v = $v ? $v : '';
                $data[] = $newcurrentdata . '[' . urlencode($k) . ']=' . urlencode($v);
            }
        }
    }

    /**
     * Format postdata for curlcall.
     *
     * @param mixed $postdata Postdata.
     * @return mixed The result of the operation.
     */
    private function format_postdata_for_curlcall($postdata) {
        if (is_object($postdata)) {
            $postdata = (array) $postdata;
        }
        $data = [];
        foreach ($postdata as $k => $v) {
            if (is_object($v)) {
                $v = (array) $v;
            }
            if (is_array($v)) {
                $currentdata = urlencode($k);
                $this->format_array_postdata_for_curlcall($v, $currentdata, $data);
            } else {
                $data[] = urlencode($k) . '=' . urlencode($v);
            }
        }
        $convertedpostdata = implode('&', $data);
        return $convertedpostdata;
    }



    /**
     * Get file.
     *
     * @param mixed $file File.
     * @return mixed The result of the operation.
     */
    public function get_file($file) {
        $connectionmethod = get_config('auth_joomdle', 'connection_method');

        if ($connectionmethod == 'fgc') {
            $response = file_get_contents($file, false, null);
        } else {
            $response = $this->get_file_curl($file);
        }

        return $response;
    }

    /**
     * Get file curl.
     *
     * @param mixed $file File.
     * @return mixed The result of the operation.
     */
    private function get_file_curl($file) {
        $curl = new curl();
        return $curl->get($file);
    }

    /**
     * Download an avatar safely to a temporary file.
     *
     * @param string $url Avatar URL.
     * @param string $joomlaurl Configured Joomla URL.
     * @return string|false Temporary file path, or false when the download is rejected.
     */
    private function download_avatar(string $url, string $joomlaurl) {
        if (!$this->urls_have_same_origin($url, $joomlaurl)) {
            return false;
        }

        $connectionmethod = get_config('auth_joomdle', 'connection_method');
        if ($connectionmethod === 'fgc') {
            $tmpfile = $this->download_avatar_fgc($url);
        } else {
            $tmpfile = $this->download_avatar_curl($url);
        }

        if (
            $tmpfile !== false &&
                (filesize($tmpfile) > self::AVATAR_MAX_SIZE || !$this->is_allowed_avatar_type($tmpfile))
        ) {
            unlink($tmpfile);
            return false;
        }

        return $tmpfile;
    }

    /**
     * Download an avatar with Moodle's cURL client.
     *
     * @param string $url Avatar URL.
     * @return string|false Temporary file path, or false on failure.
     */
    private function download_avatar_curl(string $url) {
        global $CFG;

        $tmpfile = tempnam($CFG->dataroot . '/temp', 'tmp_pic_');
        if ($tmpfile === false) {
            return false;
        }

        $curl = new curl();
        $result = $curl->download_one($url, null, [
            'filepath' => $tmpfile,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_TIMEOUT' => self::AVATAR_TIMEOUT,
            'CURLOPT_FOLLOWLOCATION' => false,
            'CURLOPT_MAXREDIRS' => 0,
            'CURLOPT_MAXFILESIZE' => self::AVATAR_MAX_SIZE,
        ]);

        $info = $curl->get_info();
        $httpcode = (int) ($info['http_code'] ?? 0);
        if (
            $result !== true || $httpcode < 200 || $httpcode >= 300 ||
                !is_file($tmpfile)
        ) {
            if (file_exists($tmpfile)) {
                unlink($tmpfile);
            }
            return false;
        }

        return $tmpfile;
    }

    /**
     * Download an avatar with file_get_contents.
     *
     * @param string $url Avatar URL.
     * @return string|false Temporary file path, or false on failure.
     */
    private function download_avatar_fgc(string $url) {
        global $CFG;

        $securityhelper = new \core\files\curl_security_helper();
        if ($securityhelper->url_is_blocked($url)) {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'follow_location' => 0,
                'ignore_errors' => true,
                'max_redirects' => 0,
                'timeout' => self::AVATAR_TIMEOUT,
                'user_agent' => \core_useragent::get_moodlebot_useragent(),
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $contents = file_get_contents($url, false, $context, 0, self::AVATAR_MAX_SIZE + 1);
        if (
            $contents === false || strlen($contents) > self::AVATAR_MAX_SIZE ||
                !$this->is_successful_http_response($http_response_header ?? [])
        ) {
            return false;
        }

        $tmpfile = tempnam($CFG->dataroot . '/temp', 'tmp_pic_');
        if ($tmpfile === false) {
            return false;
        }

        if (file_put_contents($tmpfile, $contents) !== strlen($contents)) {
            unlink($tmpfile);
            return false;
        }

        return $tmpfile;
    }

    /**
     * Check that the response headers contain a successful final HTTP status.
     *
     * @param array $headers HTTP response headers.
     * @return bool
     */
    private function is_successful_http_response(array $headers): bool {
        $httpcode = 0;
        foreach ($headers as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#i', $header, $matches)) {
                $httpcode = (int) $matches[1];
            }
        }

        return $httpcode >= 200 && $httpcode < 300;
    }

    /**
     * Check that two HTTP URLs have the same scheme, host and effective port.
     *
     * @param string $url URL to check.
     * @param string $originurl Configured origin URL.
     * @return bool
     */
    private function urls_have_same_origin(string $url, string $originurl): bool {
        $urlparts = parse_url($url);
        $originparts = parse_url($originurl);

        if (
            $urlparts === false || $originparts === false ||
                empty($urlparts['scheme']) || empty($urlparts['host']) ||
                empty($originparts['scheme']) || empty($originparts['host'])
        ) {
            return false;
        }

        $scheme = strtolower($urlparts['scheme']);
        $originscheme = strtolower($originparts['scheme']);
        if (
            !in_array($scheme, ['http', 'https'], true) || $scheme !== $originscheme ||
                strtolower($urlparts['host']) !== strtolower($originparts['host'])
        ) {
            return false;
        }

        $port = $urlparts['port'] ?? ($scheme === 'https' ? 443 : 80);
        $originport = $originparts['port'] ?? ($originscheme === 'https' ? 443 : 80);
        return $port === $originport;
    }

    /**
     * Check the downloaded avatar's actual MIME type.
     *
     * @param string $filepath File path.
     * @return bool
     */
    private function is_allowed_avatar_type(string $filepath): bool {
        $fileinfo = new finfo(FILEINFO_MIME_TYPE);
        $mimetype = $fileinfo->file($filepath);

        return in_array($mimetype, [
            'image/gif',
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true);
    }

    /**
     * System check.
     * @return mixed The result of the operation.
     */
    public function system_check() {
        $system['joomdle_auth'] = (int) is_enabled_auth('joomdle');

        $joomlaurl = get_config('auth_joomdle', 'joomla_url');
        if ($joomlaurl == '') {
            $system['joomdle_configured'] = 0;
        } else {
            $system['joomdle_configured'] = 1;
            $data = $this->call_method("test", [], true);
            $system['test_data'] = $data;
        }

        $connectionmethod = get_config('auth_joomdle', 'connection_method');
        $system['curl_blocked'] = 0;
        if ($connectionmethod == 'curl') {
            $curl = new curl();
            $securityhelper = $curl->get_security();
            if ($securityhelper->url_is_blocked($joomlaurl)) {
                $system['curl_blocked'] = 1;
            }
        }

        // Joomdle version.
        $pluginman = core_plugin_manager::instance();
        $pluginfo = $pluginman->get_plugin_info('auth_joomdle');
        $system['release'] = $pluginfo->release;

        return $system;
    }

    /**
     * Get moodle version.
     * @return mixed The result of the operation.
     */
    public function get_moodle_version() {
        global $CFG;

        return (int) $CFG->version;
    }

    /**
     * My courses.
     *
     * @param mixed $username Username.
     * @param mixed $orderbycat Order by cat.
     * @return mixed The result of the operation.
     */
    public function my_courses($username, $orderbycat = 0) {
        global $CFG, $DB;

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        if ($orderbycat) {
            $c = enrol_get_users_courses($user->id, true, ['summary'], 'category, sortorder ASC');
        } else {
            $c = enrol_get_users_courses($user->id, true, ['summary']);
        }

        $options['noclean'] = true;
        $courses = [];
        $i = 0;
        foreach ($c as $course) {
            $record = [];
            $record['id'] = $course->id;
            $record['fullname'] = $course->fullname;
            $record['category'] = $course->category;
            $record['cat_name'] = $this->get_cat_name($course->category);
            $record['summary'] = $course->summary;

            $context = context_course::instance($course->id);
            $record['summary'] = file_rewrite_pluginfile_urls(
                $record['summary'],
                'pluginfile.php',
                $context->id,
                'course',
                'summary',
                null
            );
            $record['summary'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $record['summary']);
            $record['summary'] = format_text($record['summary'], FORMAT_MOODLE, $options);

            // Check if user can self-unenrol.
            if (
                (has_capability('enrol/manual:unenrolself', $context, $user->id)) ||
                (has_capability('enrol/self:unenrolself', $context, $user->id))
            ) {
                $record['can_unenrol'] = 1;
            } else {
                $record['can_unenrol'] = 0;
            }

            $record['summary_files'] = [];
            $courseobj = new \core_course_list_element(get_course($course->id));
            foreach ($courseobj->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $url = file_encode_url(
                    "$CFG->wwwroot/auth/joomdle/pluginfile_joomdle.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    !$isimage
                );

                $urlitem = [];
                $urlitem['url'] = $url;
                $record['summary_files'][] = $urlitem;
            }

            $courses[$i] = $record;
            $i++;
        }

        // Re-sort by category sort order if requested.
        if ($orderbycat) {
            // Get categories by sort order.
            $query = "SELECT * from {$CFG->prefix}course_categories order by sortorder";
            $records = $DB->get_records_sql($query);
            $cats = [];
            foreach ($records as $record) {
                $cats[$record->id] = [];
            }

            // Fill the array used for sorting.
            foreach ($courses as $course) {
                $cats[$course['category']][] = $course;
            }

            $courses = [];
            foreach ($cats as $cat) {
                foreach ($cat as $course) {
                    $courses[] = $course;
                }
            }
        }

        return $courses;
    }

    /**
     * Returns course list
     *
     * @param int $available If true, return only self enrollable courses
     */
    public function list_courses($available = 0, $sortby = 'created', $guest = 0, $username = '', $includehidden = 0) {
        global $CFG, $DB;

        $sortby = joomdle_get_course_sort_order($sortby);

        $query = "SELECT
            co.id AS remoteid,
            ca.id AS cat_id,
            co.sortorder,
            co.fullname,
            co.shortname,
            co.idnumber,
            co.summary,
            co.startdate,
            co.timecreated AS created,
            co.timemodified AS modified,
            ca.name AS cat_name,
            ca.description AS cat_description
            FROM
            {$CFG->prefix}course_categories ca
            JOIN
            {$CFG->prefix}course co ON
            ca.id = co.category
            ";

        if (!$includehidden) {
            $query .= " WHERE co.visible = '1'";
        }

        $query .= "ORDER BY $sortby";

        $records = $DB->get_records_sql($query);

        if ($username) {
            $user = get_complete_user_data('username', $username);
            $c = enrol_get_users_courses($user->id, true);

            $mycourses = [];
            foreach ($c as $course) {
                $mycourses[] = $course->id;
            }
        }

        $i = 0;
        $now = time();
        $options['noclean'] = true;
        $cursos = [];
        foreach ($records as $curso) {
            $enrolmethods = enrol_get_instances($curso->remoteid, true);

            $c = get_object_vars($curso);

            $c['self_enrolment'] = 0;
            $c['guest'] = 0;
            $in = true;
            foreach ($enrolmethods as $instance) {
                if (($instance->enrol == 'paypal') || ($instance->enrol == 'joomdle')) {
                    $enrol = $instance->enrol;
                    $query = "SELECT cost, currency
                                FROM {$CFG->prefix}enrol
                                where courseid = ? and enrol = ?";
                    $params = [$curso->remoteid, $enrol];
                    $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
                    $c['cost'] = (float) $record->cost;
                    $c['currency'] = $record->currency;
                }

                // Self-enrolment.
                if ($instance->enrol == 'self') {
                    $c['self_enrolment'] = 1;
                }

                // Guest access.
                if ($instance->enrol == 'guest') {
                    $c['guest'] = 1;
                }

                if (($instance->enrolstartdate) && ($instance->enrolenddate)) {
                    $in = false;
                    if (($instance->enrolstartdate <= $now) && ($instance->enrolenddate >= $now)) {
                        $in = true;
                    }
                } else if ($instance->enrolstartdate) {
                    $in = false;
                    if (($instance->enrolstartdate <= $now)) {
                        $in = true;
                    }
                } else if ($instance->enrolenddate) {
                    $in = false;
                    if ($instance->enrolenddate >= $now) {
                        $in = true;
                    }
                }
            }

            // Check if only guest courses are wanted.
            if (($guest) && (!$c['guest'])) {
                continue;
            }

            // Skip not self-enrolable courses if param says so.
            if (($available) && (!$c['self_enrolment'])) {
                continue;
            }

            $c['in_enrol_date'] = $in;

            $c['enroled'] = 0;
            if ($username) {
                if (in_array($curso->remoteid, $mycourses)) {
                    $c['enroled'] = 1;
                }
            }

            $c['fullname'] = format_string($c['fullname']);
            $c['cat_name'] = format_string($c['cat_name']);

            $context = context_course::instance($curso->remoteid);
            $c['summary'] = file_rewrite_pluginfile_urls($c['summary'], 'pluginfile.php', $context->id, 'course', 'summary', null);
            $c['summary'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['summary']);
            $c['summary'] = format_text($c['summary'], FORMAT_MOODLE, $options);

            $context = context_coursecat::instance($curso->cat_id);
            $c['cat_description'] = file_rewrite_pluginfile_urls(
                $c['cat_description'],
                'pluginfile.php',
                $context->id,
                'coursecat',
                'description',
                null
            );
            $c['cat_description'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['cat_description']);
            $c['cat_description'] = format_text($c['cat_description'], FORMAT_MOODLE, $options);

            $c['summary_files'] = [];
            $course = new \core_course_list_element(get_course($curso->remoteid));
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $url = file_encode_url(
                    "$CFG->wwwroot/auth/joomdle/pluginfile_joomdle.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    !$isimage
                );

                $urlitem = [];
                $urlitem['url'] = $url;
                $c['summary_files'][] = $urlitem;
            }

            $cursos[$i] = $c;

            $i++;
        }

        return ($cursos);
    }

    /**
     * Returns course list based on start chars
     *
     * @param start_chars: return courses that starts with these chars
     */
    public function courses_abc($startchars, $username) {
        global $CFG, $DB;

        $charsarray = str_split($startchars);
        $likes = [];
        $params = [];
        foreach ($charsarray as $c) {
            $cond = "$c%";

            $like = $DB->sql_like('fullname', '?', false);
            $likes[] = $like;
            $params[] = $cond;
        }
        $where          = '(' . implode(' OR ', $likes) . ')';

        $query = "SELECT
            co.id          AS remoteid,
            ca.id          AS cat_id,
            co.sortorder,
            co.fullname,
            co.shortname,
            co.idnumber,
            co.summary,
            co.startdate,
            co.timecreated as created,
            co.timemodified as modified,
            ca.name        AS cat_name,
            ca.description AS cat_description
            FROM
            {$CFG->prefix}course_categories ca
            JOIN
            {$CFG->prefix}course co ON
            ca.id = co.category
            WHERE
            co.visible = '1'  AND
            $where
            ORDER BY
            fullname
            ";
        $records = $DB->get_records_sql($query, $params);

        if ($username) {
            $user = get_complete_user_data('username', $username);
            $c = enrol_get_users_courses($user->id, true);

            $mycourses = [];
            foreach ($c as $course) {
                $mycourses[] = $course->id;
            }
        }

        $now = time();
        $options['noclean'] = true;
        $cursos = [];
        foreach ($records as $curso) {
            $c = get_object_vars($curso);

            $c['self_enrolment'] = 0;
            $c['guest'] = 0;
            $in = true;
            $enrolmethods = enrol_get_instances($curso->remoteid, true);
            foreach ($enrolmethods as $instance) {
                if (($instance->enrol == 'paypal') || ($instance->enrol == 'joomdle')) {
                    $enrol = $instance->enrol;
                    $query = "SELECT cost, currency
                                FROM {$CFG->prefix}enrol
                                where courseid = ? and enrol = ?";
                    $params = [$curso->remoteid, $enrol];
                    $record = $DB->get_record_sql($query, $params);
                    $c['cost'] = (float) $record->cost;
                    $c['currency'] = $record->currency;
                }

                // Self-enrolment.
                if ($instance->enrol == 'self') {
                    $c['self_enrolment'] = 1;
                }

                // Guest access.
                if ($instance->enrol == 'guest') {
                    $c['guest'] = 1;
                }

                if (($instance->enrolstartdate) && ($instance->enrolenddate)) {
                    $in = false;
                    if (($instance->enrolstartdate <= $now) && ($instance->enrolenddate >= $now)) {
                        $in = true;
                    }
                } else if ($instance->enrolstartdate) {
                    $in = false;
                    if (($instance->enrolstartdate <= $now)) {
                        $in = true;
                    }
                } else if ($instance->enrolenddate) {
                    $in = false;
                    if ($instance->enrolenddate >= $now) {
                        $in = true;
                    }
                }
            }
            $c['in_enrol_date'] = $in;

            $c['enroled'] = 0;
            if ($username) {
                if (in_array($curso->remoteid, $mycourses)) {
                    $c['enroled'] = 1;
                }
            }

            $c['fullname'] = format_string($c['fullname']);
            $c['cat_name'] = format_string($c['cat_name']);

            $context = context_course::instance($curso->remoteid);
            $c['summary'] = file_rewrite_pluginfile_urls(
                $c['summary'],
                'pluginfile.php',
                $context->id,
                'course',
                'summary',
                null
            );
            $c['summary'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['summary']);
            $c['summary'] = format_text($c['summary'], FORMAT_MOODLE, $options);

            $c['summary_files'] = [];
            $course = new \core_course_list_element(get_course($curso->remoteid));
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $url = file_encode_url(
                    "$CFG->wwwroot/auth/joomdle/pluginfile_joomdle.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    !$isimage
                );

                $urlitem = [];
                $urlitem['url'] = $url;
                $c['summary_files'][] = $urlitem;
            }

            $cursos[] = $c;
        }

        return ($cursos);
    }

    /**
     * Returns course category lis
     *
     * @param int $cat Parent category
     */
    public function get_course_categories($cat = 0) {
        global $CFG, $DB;

        $cat = clean_param($cat, PARAM_INT);

        if ($cat == -1) {
            $query = "SELECT id, name, description
                FROM
                {$CFG->prefix}course_categories
                WHERE
                visible = '1'
                ORDER BY
                sortorder ASC
                ";
            $params = [];
        } else {
            $query = "SELECT id, name, description
                FROM
                {$CFG->prefix}course_categories
                WHERE
                visible = '1' AND
                parent = ?
                ORDER BY
                sortorder ASC
                ";
            $params = [$cat];
        }
        $records = $DB->get_records_sql($query, $params);

        $options['noclean'] = true;
        $cats = [];
        foreach ($records as $cat) {
            $c = get_object_vars($cat);
            $c['name'] = format_string($c['name']);

            $context = context_coursecat::instance($cat->id);
            $c['description'] = file_rewrite_pluginfile_urls(
                $c['description'],
                'pluginfile.php',
                $context->id,
                'coursecat',
                'description',
                null
            );
            $c['description'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['description']);
            $c['description'] = format_text($c['description'], FORMAT_MOODLE, $options);
            $cats[] = $c;
        }

        return ($cats);
    }


    /**
     * Returns courses from a specific category
     *
     * @param string $category Category ID
     * @param int $available If true, return only enrollable courses
     */
    public function courses_by_category($category, $available = 0, $username = '') {
        global $CFG, $DB;

        $where = '';
        if ($available) {
            $where = " AND co.enrollable = '1'";
        }

        $query = "SELECT
            co.id          AS remoteid,
            ca.id          AS cat_id,
            ca.name        AS cat_name,
            ca.description AS cat_description,
            co.sortorder,
            co.fullname,
            co.summary,
            co.idnumber,
            co.startdate
            FROM
            {$CFG->prefix}course_categories ca
            JOIN
            {$CFG->prefix}course co ON
            ca.id = co.category
            WHERE
            co.visible = '1' AND
            ca.id = ?
            $where
            ORDER BY
            sortorder ASC
            ";

        $params = [$category];
        $records = $DB->get_records_sql($query, $params);

        if ($username) {
            $user = get_complete_user_data('username', $username);
            $c = enrol_get_users_courses($user->id, true);

            $mycourses = [];
            foreach ($c as $course) {
                $mycourses[] = $course->id;
            }
        }

        $now = time();
        $options['noclean'] = true;
        $cursos = [];
        foreach ($records as $curso) {
            $c = get_object_vars($curso);

            // Course cost.
            $enrolmethods = enrol_get_instances($curso->remoteid, true);
            $c['self_enrolment'] = 0;
            $c['guest'] = 0;
            $in = true;
            foreach ($enrolmethods as $instance) {
                if (($instance->enrol == 'paypal') || ($instance->enrol == 'joomdle')) {
                    $enrol = $instance->enrol;
                    $query = "SELECT cost, currency
                                FROM {$CFG->prefix}enrol
                                where courseid = ? and enrol = ?";
                    $params = [$curso->remoteid, $enrol];
                    $record = $DB->get_record_sql($query, $params);
                    $c['cost'] = (float) $record->cost;
                    $c['currency'] = $record->currency;
                }

                // Self-enrolment.
                if ($instance->enrol == 'self') {
                    $c['self_enrolment'] = 1;
                }

                // Guest access.
                if ($instance->enrol == 'guest') {
                    $c['guest'] = 1;
                }

                if (($instance->enrolstartdate) && ($instance->enrolenddate)) {
                    $in = false;
                    if (($instance->enrolstartdate <= $now) && ($instance->enrolenddate >= $now)) {
                        $in = true;
                    }
                } else if ($instance->enrolstartdate) {
                    $in = false;
                    if (($instance->enrolstartdate <= $now)) {
                        $in = true;
                    }
                } else if ($instance->enrolenddate) {
                    $in = false;
                    if ($instance->enrolenddate >= $now) {
                        $in = true;
                    }
                }
            }
            $c['in_enrol_date'] = $in;

            $c['enroled'] = 0;
            if ($username) {
                if (in_array($curso->remoteid, $mycourses)) {
                    $c['enroled'] = 1;
                }
            }

            $c['fullname'] = format_string($c['fullname']);
            $c['cat_name'] = format_string($c['cat_name']);
            $context = context_coursecat::instance($c['cat_id']);
            $c['cat_description'] = file_rewrite_pluginfile_urls(
                $c['cat_description'],
                'pluginfile.php',
                $context->id,
                'coursecat',
                'description',
                null
            );
            $c['cat_description'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['cat_description']);
            $c['cat_description'] = format_text($c['cat_description'], FORMAT_MOODLE, $options);

            $context = context_course::instance($curso->remoteid);
            $c['summary'] = file_rewrite_pluginfile_urls($c['summary'], 'pluginfile.php', $context->id, 'course', 'summary', null);
            $c['summary'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['summary']);
            $c['summary'] = format_text($c['summary'], FORMAT_MOODLE, $options);

            $c['summary_files'] = [];
            $course = new \core_course_list_element(get_course($curso->remoteid));
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $url = file_encode_url(
                    "$CFG->wwwroot/auth/joomdle/pluginfile_joomdle.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    !$isimage
                );

                $urlitem = [];
                $urlitem['url'] = $url;
                $c['summary_files'][] = $urlitem;
            }

            $cursos[] = $c;
        }

        return ($cursos);
    }

    /**
     * Returns detailed info aboout a course
     *
     * @param int $id Course identifier
     */
    public function get_course_info($id, $username = '') {
        global $CFG, $DB;

        // Prepare reply for "not found" course.
        $notfound = [];
        $notfound['remoteid'] = 0;
        $notfound['cat_id'] = 0;
        $notfound['cat_name'] = "";
        $notfound['cat_description'] = "";
        $notfound['sortorder'] = "";
        $notfound['fullname'] = "";
        $notfound['shortname'] = "";
        $notfound['idnumber'] = "";
        $notfound['summary'] = "";
        $notfound['startdate'] = 0;
        $notfound['enddate'] = 0;
        $notfound['numsections'] = 0;
        $notfound['lang'] = "";
        $notfound['self_enrolment'] = 0;
        $notfound['enroled'] = 0;
        $notfound['in_enrol_date'] = false;
        $notfound['guest'] = 0;
        $notfound['visible'] = 0;
        $notfound['summary_files'] = [];

        $username = strtolower($username);

        $query = "SELECT
            co.id          AS remoteid,
            ca.id          AS cat_id,
            ca.name        AS cat_name,
            ca.description AS cat_description,
            co.sortorder,
            co.fullname,
            co.shortname,
            co.idnumber,
            co.summary,
            co.startdate,
            co.enddate,
            co.visible,
            co.lang
            FROM
            {$CFG->prefix}course_categories ca
            JOIN
            {$CFG->prefix}course co ON
            ca.id = co.category
            WHERE
            co.id = ?
            ORDER BY
            sortorder ASC";

        $params = [$id];
        $record = $DB->get_record_sql($query, $params);

        if (!$record) {
            return $notfound;
        }

        $options['noclean'] = true;

        $courseinfo = get_object_vars($record);
        $courseinfo['fullname'] = format_string($courseinfo['fullname']);
        $courseinfo['cat_name'] = format_string($courseinfo['cat_name']);
        $context = context_coursecat::instance($courseinfo['cat_id']);
        $courseinfo['cat_description'] = file_rewrite_pluginfile_urls(
            $courseinfo['cat_description'],
            'pluginfile.php',
            $context->id,
            'coursecat',
            'description',
            null
        );
        $courseinfo['cat_description'] = str_replace(
            'pluginfile.php',
            '/auth/joomdle/pluginfile_joomdle.php',
            $courseinfo['cat_description']
        );
        $courseinfo['cat_description'] = format_text($courseinfo['cat_description'], FORMAT_MOODLE, $options);

        $context = context_course::instance($record->remoteid);
        $courseinfo['summary'] = file_rewrite_pluginfile_urls(
            $courseinfo['summary'],
            'pluginfile.php',
            $context->id,
            'course',
            'summary',
            null
        );
        $courseinfo['summary'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $courseinfo['summary']);
        $courseinfo['summary'] = format_text($courseinfo['summary'], FORMAT_MOODLE, $options);

        $params = [$id];
        $query = "SELECT count(*)
            FROM
            {$CFG->prefix}course_sections
            WHERE
            course = ? and section != 0 and visible=1
            ";

        $courseinfo['numsections'] = $DB->count_records_sql($query, $params);

        $courseinfo['self_enrolment'] = 0;
        $courseinfo['guest'] = 0;
        $in = true;
        $now = time();
        /* Get course cost if any  and other enrolment related info */
        $instances = enrol_get_instances($id, true);
        foreach ($instances as $instance) {
            if (($instance->enrol == 'paypal') || ($instance->enrol == 'joomdle')) {
                $enrol = $instance->enrol;
                $query = "SELECT cost, currency
                            FROM {$CFG->prefix}enrol
                            where courseid = ? and enrol = ?";
                $params = [$id, $enrol];
                $record = $DB->get_record_sql($query, $params);
                $courseinfo['cost'] = (float) $record->cost;
                $courseinfo['currency'] = $record->currency;
            }

            /* Get enrolment dates. We get the last one, as good/bad as any other */
            if ($instance->enrolstartdate) {
                $courseinfo['enrolstartdate'] = $instance->enrolstartdate;
            }
            if ($instance->enrolenddate) {
                $courseinfo['enrolenddate'] = $instance->enrolenddate;
            }

            if ($instance->enrolperiod) {
                $courseinfo['enrolperiod'] = $instance->enrolperiod;
            }

            // Self-enrolment.
            if ($instance->enrol == 'self') {
                $courseinfo['self_enrolment'] = 1;
            }

            // Guest access.
            if ($instance->enrol == 'guest') {
                $courseinfo['guest'] = 1;
            }

            if (($instance->enrolstartdate) && ($instance->enrolenddate)) {
                $in = false;
                if (($instance->enrolstartdate <= $now) && ($instance->enrolenddate >= $now)) {
                    $in = true;
                }
            } else if ($instance->enrolstartdate) {
                $in = false;
                if (($instance->enrolstartdate <= $now)) {
                    $in = true;
                }
            } else if ($instance->enrolenddate) {
                $in = false;
                if ($instance->enrolenddate >= $now) {
                    $in = true;
                }
            }
        }

        $courseinfo['in_enrol_date'] = $in;

        $courseinfo['enroled'] = 0;
        if ($username) {
            $user = get_complete_user_data('username', $username);
            $courses = enrol_get_users_courses($user->id, true);

            $mycourses = [];
            foreach ($courses as $course) {
                $mycourses[] = $course->id;
            }
            if (in_array($id, $mycourses)) {
                $courseinfo['enroled'] = 1;
            }
        }

        $courseinfo['summary_files'] = [];
        $course = new \core_course_list_element(get_course($id));
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url(
                "$CFG->wwwroot/auth/joomdle/pluginfile_joomdle.php",
                '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                !$isimage
            );

            $urlitem = [];
            $urlitem['url'] = $url;
            $courseinfo['summary_files'][] = $urlitem;
        }

        return $courseinfo;
    }

    /**
     * Returns course topics
     *
     * @param int $id Course identifier
     */
    public function get_course_contents($id) {
        global $CFG, $DB;

        $query = "SELECT
            cs.id,
            cs.section,
            cs.name,
            cs.summary
            FROM
            {$CFG->prefix}course_sections cs
            WHERE
            cs.course = ?
            and cs.visible = 1
            ORDER by cs.section;
            ";

        $params = [$id];
        $records = $DB->get_records_sql($query, $params);

        $context = context_course::instance($id);

        $options['noclean'] = true;
        $data = [];
        foreach ($records as $r) {
            $e['section'] = $r->section;
            $e['section'] = format_string($e['section']);
            $e['name'] = $r->name;
            $e['summary'] = $r->summary;
            $e['summary'] = file_rewrite_pluginfile_urls($r->summary, 'pluginfile.php', $context->id, 'course', 'section', $r->id);
            $e['summary'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $e['summary']);
            $e['summary'] = format_text($e['summary'], FORMAT_MOODLE, $options);

            $data[] = $e;
        }

        return $data;
    }

    /**
     * Returns editing teachers
     *
     * @param int $id Course identifier
     */
    public function get_course_editing_teachers($id) {
        global $CFG;

        $id = addslashes($id);
        $context = context_course::instance($id);
        $profs = get_role_users(self::ROLE_TEACHER, $context);

        $data = [];
        $i = 0;
        foreach ($profs as $p) {
            $e['firstname'] = $p->firstname;
            $e['lastname'] = $p->lastname;
            $e['username'] = $p->username;

            $data[$i] = $e;
            $i++;
        }

        return $data;
    }

    /**
     * Teachers abc.
     *
     * @param mixed $startchars Start chars.
     * @return mixed The result of the operation.
     */
    public function teachers_abc($startchars) {
        global $CFG, $DB;

        $charsarray = str_split($startchars);
        $likes = [];
        $params = [];
        foreach ($charsarray as $c) {
            $cond = "$c%";

            $like = $DB->sql_like('u.lastname', '?', false);
            $likes[] = $like;
            $params[] = $cond;
        }
        $where = '(' . implode(' OR ', $likes) . ')';

        $teacherrole = self::ROLE_TEACHER;
        $query = "SELECT distinct (u.id), u.username, u.firstname, u.lastname
                 FROM {$CFG->prefix}course as c, {$CFG->prefix}role_assignments AS ra,
                {$CFG->prefix}user AS u, {$CFG->prefix}context AS ct
                 WHERE c.id = ct.instanceid AND ra.roleid = $teacherrole AND ra.userid = u.id AND ct.id = ra.contextid
                     AND c.visible=1 and u.suspended=0 AND $where";

        $query .= " ORDER BY lastname, firstname";

        $records = $DB->get_records_sql($query, $params);
        $data = [];
        foreach ($records as $p) {
            $e = [];
            $e['firstname'] = $p->firstname;
            $e['lastname'] = $p->lastname;
            $e['username'] = $p->username;

            $data[] = $e;
        }

        return $data;
    }

    /**
     * Teacher courses.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function teacher_courses($username) {
        global $CFG, $DB;

        $username = strtolower($username);

        $teacherrole = self::ROLE_TEACHER;
        $query = " SELECT distinct c.id as remoteid, c.fullname, ca.name as cat_name, ca.id as cat_id
                    FROM {$CFG->prefix}course as c, {$CFG->prefix}role_assignments AS ra,
                    {$CFG->prefix}user AS u, {$CFG->prefix}context AS ct,  {$CFG->prefix}course_categories ca
                    WHERE c.id = ct.instanceid AND ra.roleid = $teacherrole AND ra.userid = u.id AND
                    ct.id = ra.contextid AND ca.id = c.category and u.username = ? and c.visible=1";

        $params = [$username];
        $records = $DB->get_records_sql($query, $params);
        $data = [];
        $i = 0;
        foreach ($records as $p) {
            $e['remoteid'] = $p->remoteid;
            $e['fullname'] = $p->fullname;
            $e['fullname'] = format_string($e['fullname']);
            $e['cat_id'] = $p->cat_id;
            $e['cat_name'] = $p->cat_name;
            $e['cat_name'] = format_string($e['cat_name']);

            $data[$i] = $e;
            $i++;
        }

        return $data;
    }

    /**
     * Get my grades.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function get_my_grades($username) {
        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        $courses = enrol_get_users_courses($user->id, true);

        $news = [];
        foreach ($courses as $c) {
            $coursenews['remoteid'] = $c->id;
            $coursenews['fullname'] = $c->fullname;
            $coursenews['fullname'] = format_string($coursenews['fullname']);
            $coursenews['grades'] = $this->get_user_grades($username, $c->id);

            $news[] = $coursenews;
        }

        return $news;
    }

    /**
     * Returns grades of a student for each task in a course
     *
     * @param string $user Username
     * @param int $cid Course identifier
     */
    public function get_user_grades($username, $cid) {
        global $CFG, $DB;

        $username = strtolower($username);
        $user = get_complete_user_data('username', $username);
        $uid = $user->id;

        $sql = "SELECT g.itemid, g.finalgrade,gi.courseid,gi.itemname,gi.id, g.timemodified
                    FROM {$CFG->prefix}grade_items gi
                    JOIN {$CFG->prefix}grade_grades g      ON g.itemid = gi.id
                    JOIN {$CFG->prefix}user u              ON u.id = g.userid
                    JOIN {$CFG->prefix}role_assignments ra ON ra.userid = u.id
                    WHERE g.finalgrade IS NOT NULL
                    AND u.id =  ?
                    AND gi.courseid = ?
                    GROUP BY g.itemid, g.finalgrade, gi.courseid, gi.itemname,gi.id, g.timemodified";

        $sumarray = [];
        $params = [$uid, $cid];
        if ($sums = $DB->get_records_sql($sql, $params)) {
            $i = 0;
            $rdo = [];
            foreach ($sums as $sum) {
                if (! $gradegrade = grade_grade::fetch(['itemid' => $sum->id, 'userid' => $uid])) {
                    $gradegrade = new grade_grade();
                    $gradegrade->userid = $user->id;
                    $gradegrade->itemid = null;
                }

                $gradeitem = $gradegrade->load_grade_item();

                $sums2[$i] = $sum;
                $scale = $gradeitem->load_scale();
                $formattedgrade = grade_format_gradevalue($sums2[$i]->finalgrade, $gradeitem, true, GRADE_DISPLAY_TYPE_REAL);

                $sums2[$i]->finalgrade = $formattedgrade;

                $rdo[$i]['itemname'] = $sum->itemname;
                $rdo[$i]['timemodified'] = $sum->timemodified;
                $rdo[$i]['finalgrade'] = $formattedgrade;

                $i++;
            }
            return $rdo;
        }

        return [];
    }

    /**
     * Get my grade user report.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function get_my_grade_user_report($username) {
        global $CFG, $DB;

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        $courses = enrol_get_users_courses($user->id, true);

        $news = [];
        foreach ($courses as $c) {
            $coursenews['remoteid'] = $c->id;
            $coursenews['fullname'] = $c->fullname;
            $coursenews['fullname'] = format_string($coursenews['fullname']);
            $coursenews['grades'] = $this->get_grade_user_report($c->id, $username);

            $news[] = $coursenews;
        }

        return $news;
    }

    /**
     * Get grade user report.
     *
     * @param mixed $id Id.
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function get_grade_user_report($id, $username) {
        global $CFG, $DB;

        $username = strtolower($username);
        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return [];
        }

        $gpr = new grade_plugin_return(['type' => 'report', 'plugin' => 'user', 'courseid' => $id, 'userid' => $user->id]);
        $context = context_course::instance($id);
        $report = new user_report($id, $gpr, $context, $user->id);

        // Get course total.
        $query = "select id
            from  {$CFG->prefix}grade_items
            where courseid = ?
            AND itemtype='course'";
        $params = [$id];
        $catitem = $DB->get_record_sql($query, $params);

        $query = "SELECT g.finalgrade,g.rawgrademax,g.rawgrademin
          FROM {$CFG->prefix}grade_grades g
         WHERE g.itemid = ?
           AND g.userid =  ?";
        $params = [$catitem->id, $user->id];

        $grade = $DB->get_record_sql($query, $params);

        $total['fullname'] = '';
        if ($grade) {
            $total['finalgrade'] = (float) $grade->finalgrade;
            $total['grademax'] = (float) $grade->rawgrademax;
            $total['grademin'] = (float) $grade->rawgrademin;
        } else {
            $total['finalgrade'] = (float) 0;
            $total['grademax'] = (float) 0;
            $total['grademin'] = (float) 0;
        }
        $total['items'] = [];
        $total['letter'] = '';

        if (! $gradegrade = grade_grade::fetch(['itemid' => $catitem->id, 'userid' => $user->id])) {
            $gradegrade = new grade_grade();
            $gradegrade->userid = $user->id;
            $gradegrade->itemid = $catitem->id;
        }
        $gradegrade->load_grade_item();

        $total['letter'] = grade_format_gradevalue($total['finalgrade'], $gradegrade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);

        $data = [];
        $data[] = $total;

        $query = "select {$CFG->prefix}grade_categories.fullname, {$CFG->prefix}grade_items.id,
                    {$CFG->prefix}grade_items.grademax, {$CFG->prefix}grade_categories.id
                    from {$CFG->prefix}grade_categories, {$CFG->prefix}grade_items
                    where {$CFG->prefix}grade_categories.id = {$CFG->prefix}grade_items.iteminstance
                    and {$CFG->prefix}grade_items.courseid = ? and (itemtype='category' or itemtype='course')";

        $params = [$id];
        $cats = $DB->get_records_sql($query, $params);

        foreach ($cats as $r) {
            if ($r->fullname != '?') {
                $e['fullname'] = $r->fullname;
            } else {
                $e['fullname'] = '';
            }
            $e['grademax'] = (float) $r->grademax;

            $catid = $r->id;

            // Get category grade total.
            $query = "select id
                from  {$CFG->prefix}grade_items
                where iteminstance = ?
                AND courseid = ?
                AND itemtype = 'category'";
            $params = [$catid, $id];
            $catitem = $DB->get_record_sql($query, $params);

            $query = "SELECT g.finalgrade
              FROM {$CFG->prefix}grade_grades g
             WHERE g.itemid = ?
               AND g.userid =  ?";
            $params = [$catitem->id, $user->id];

            $grade = $DB->get_record_sql($query, $params);
            if ($grade) {
                $e['finalgrade'] = (float) $grade->finalgrade;
            } else {
                $e['finalgrade'] = (float) 0;
            }

            if (! $gradegrade = grade_grade::fetch(['itemid' => $catitem->id, 'userid' => $user->id])) {
                $gradegrade = new grade_grade();
                $gradegrade->userid = $user->id;
                $gradegrade->itemid = $catitem->id;
            }
            $gradegrade->load_grade_item();

            $e['letter'] = grade_format_gradevalue($e['finalgrade'], $gradegrade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);

            // Get items.
            $query = "select *
                from  {$CFG->prefix}grade_items
                where categoryId = ?";
            $query .= ' AND hidden = 0';
            $query .= " order by sortorder";

            $params = [$catid];
            $items = $DB->get_records_sql($query, $params);
            $categoryitems = [];

            if (count($items) == 0) {
                continue;
            }

            foreach ($items as $item) {
                $categoryitem['name'] = $item->itemname;
                $categoryitem['grademin'] = $item->grademin;
                $categoryitem['grademax'] = $item->grademax;

                $categoryitem['module'] = $item->itemmodule;
                $categoryitem['iteminstance'] = $item->iteminstance;

                $conditions = ['name' => $item->itemmodule];
                $module = $DB->get_record('modules', $conditions);

                if ($module) {
                    $conditions = ['course' => $item->courseid, 'module' => $module->id, 'instance' => $item->iteminstance];
                    $cm = $DB->get_record('course_modules', $conditions);

                    $categoryitem['course_module_id'] = $cm->id;

                    switch ($item->itemmodule) {
                        case 'quiz':
                            $conditions = ['id' => $item->iteminstance];
                            $quiz = $DB->get_record('quiz', $conditions);
                            $categoryitem['due'] = $quiz->timeclose;
                            break;
                        case 'assignment':
                            $conditions = ['id' => $item->iteminstance];
                            $assignment = $DB->get_record('assignment', $conditions);
                            $categoryitem['due'] = $assignment->timedue;
                            break;
                        default:
                            $categoryitem['due'] = 0;
                            break;
                    }
                } else {
                    $categoryitem['course_module_id'] = 0;
                    $categoryitem['due'] = 0;
                }

                $query = "SELECT g.finalgrade, g.feedback
                  FROM {$CFG->prefix}grade_grades g
                 WHERE g.itemid = ?
                   AND g.userid =  ?";
                $params = [$item->id, $user->id];

                $grade = $DB->get_record_sql($query, $params);

                if (! $gradegrade = grade_grade::fetch(['itemid' => $item->id, 'userid' => $user->id])) {
                    $gradegrade = new grade_grade();
                    $gradegrade->userid = $user->id;
                    $gradegrade->itemid = $item->id;
                }

                $gradegrade->load_grade_item();

                if (($grade) && ($grade->finalgrade !== null)) {
                    $categoryitem['finalgrade'] = (float) $grade->finalgrade;
                    // Format the final grade for display.
                    $formattedgrade = grade_format_gradevalue($grade->finalgrade, $gradegrade->grade_item, true);
                    $categoryitem['finalgrade'] = $formattedgrade;
                    $categoryitem['feedback'] = $grade->feedback;
                    if ($report->showlettergrade) {
                        $categoryitem['letter'] = grade_format_gradevalue(
                            $grade->finalgrade,
                            $gradegrade->grade_item,
                            true,
                            GRADE_DISPLAY_TYPE_LETTER
                        );
                    } else {
                        $categoryitem['letter'] = '';
                    }
                } else {
                    $categoryitem['finalgrade'] = (float) -1;
                    $categoryitem['feedback'] = '';
                    $categoryitem['letter'] = '';
                }

                $categoryitems[] = $categoryitem;
            }

            $e['items'] = $categoryitems;

            $data[] = $e;
        }

        // Don't return total if there is nothing else.
        if (count($data) == 1) {
            $rdo['data'] = [];
        } else {
            $rdo['data'] = $data;
        }

        $rdo['config']['showlettergrade'] = (int) $report->showlettergrade;

        return $rdo;
    }

    /**
     * Get user grade.
     *
     * @param mixed $userid User id.
     * @param mixed $itemid Item id.
     * @return mixed The result of the operation.
     */
    public function get_user_grade($userid, $itemid) {
        global $CFG, $DB;

        $sql = "SELECT rawgrade
            FROM {$CFG->prefix}grade_grades" .
            " WHERE itemid = ? and userid = ?";
        $params = [$itemid, $userid];
        $grade = $DB->get_records_sql($sql, $params);

        return $grade;
    }

    /**
     * Get course students.
     *
     * @param mixed $id Id.
     * @param mixed $search Search.
     * @param mixed $active Active.
     * @return mixed The result of the operation.
     */
    public function get_course_students($id, $search = '', $active = 0) {
        global $DB;

        $context = context_course::instance($id);
        $alumnos = get_role_users(self::ROLE_STUDENT, $context);

        $conditions = ['courseid' => $id, 'enrol' => 'manual'];
        $enrol = $DB->get_record('enrol', $conditions);

        if (!$enrol) {
            return [];
        }

        $students = [];

        foreach ($alumnos as $alumno) {
            if ($search) {
                if (
                    (stripos($alumno->username, $search) === false)
                    && (stripos($alumno->firstname, $search) === false)
                    && (stripos($alumno->lastname, $search) === false)
                    && (stripos($alumno->idnumber, $search) === false)
                ) {
                    continue;
                }
            }

            $include = true;
            $username = $alumno->username;
            switch ($active) {
                case 0:
                    // Skip non active enrolments.
                    $conditions = ['username' => $username];
                    $user = $DB->get_record('user', $conditions);

                    if (!$user) {
                        $include = false;
                        break;
                    }

                    $conditions = ['enrolid' => $enrol->id, 'userid' => $user->id];
                    $ue = $DB->get_record('user_enrolments', $conditions);
                    if ($ue->status) {
                        $include = false;
                    }
                    break;
                case 1:
                    // Skip active enrolments.
                    $conditions = ['username' => $username];
                    $user = $DB->get_record('user', $conditions);

                    if (!$user) {
                        $include = false;
                        break;
                    }

                    $conditions = ['enrolid' => $enrol->id, 'userid' => $user->id];
                    $ue = $DB->get_record('user_enrolments', $conditions);
                    if (!$ue->status) {
                        $include = false;
                    }
                    break;

                case 2:
                    // Return all users.
                    break;
            }

            if (!$include) {
                continue;
            }

            $a['firstname'] = $alumno->firstname;
            $a['lastname'] = $alumno->lastname;
            $a['username'] = $alumno->username;
            $a['email'] = $alumno->email;
            $a['id'] = $alumno->id;

            $students[] = $a;
        }

        return ($students);
    }


    /**
     * Returns upcoming events for a course
     *
     * @param int $id Course identifier
     */
    public function get_upcoming_events($id, $username = '') {
        global $CFG, $DB;

        $id = addslashes($id);
        $courseshown = $id;
        $coursestoload    = [$courseshown => $id];
        $groupeventsfrom = [$courseshown => 1];

        $filtercourse = $DB->get_records_list('course', 'id', $coursestoload);

        $gs = [];
        $true = true;
        [$courses, $group, $userid] = calendar_set_filters($filtercourse, $true);
        $courses = [$id => $id];

        if ($username != '') {
            // Show only events for groups where user is a member.
            $groups = groups_get_all_groups($id);
            foreach ($groups as $group) {
                $found = false;
                // Check is user is a member of the group.
                $members = $this->get_group_members($group->id);
                foreach ($members as $member) {
                    if ($member['username'] == $username) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $gs[$group->id] = $group->id;
                }
            }
        }

        $daysinfuture = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
        $maxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;
        $display = new \stdClass();
        $display->range = $daysinfuture; // How many days in the future we 'll look.
        $display->maxevents = $maxevents;
        $now = time(); // We 'll need this later.
        $usermidnighttoday = usergetmidnight($now);
        $display->tstart = $usermidnighttoday;
        $display->tend = usergetmidnight($display->tstart + DAYSECS * $display->range + 3 * HOURSECS) - 1;

        $events = calendar_get_legacy_events($display->tstart, $display->tend, [$userid], $gs, $courses);

        $data = [];
        foreach ($events as $r) {
            $e = [];
            $e['name'] = $r->name;
            $e['timestart'] = $r->timestart;
            $e['courseid'] = $r->courseid;

            $data[] = $e;
        }

        return $data;
    }

    /**
     * Returns last news for a course
     *
     * @param int $id Course identifier
     */
    public function get_news_items($id) {
        global $CFG, $DB;

        $conditions = ['id' => $id];
        $COURSE = $DB->get_record('course', $conditions);

        if (!$forum = forum_get_course_forum($COURSE->id, 'news')) {
            return [];
        }

        $modinfo = get_fast_modinfo($COURSE);
        if (empty($modinfo->instances['forum'][$forum->id])) {
            return [];
        }
        $cm = $modinfo->instances['forum'][$forum->id];

        // Get all the recent discussions we're allowed to see.
        if (! $discussions = forum_get_discussions($cm, 'p.modified DESC', false, -1, $COURSE->newsitems)) {
            return [];
        }

        $data = [];
        foreach ($discussions as $r) {
            $e['discussion'] = $r->discussion;
            $e['subject'] = $r->subject;
            $e['timemodified'] = $r->timemodified;

            $data[] = $e;
        }

        return $data;
    }

    /**
     * Returns grading system for a course
     *
     * @param int $id Course identifier
     */
    public function get_course_grade_categories($id) {
        global $CFG, $DB;
        $query = "select {$CFG->prefix}grade_categories.fullname, {$CFG->prefix}grade_items.grademin,
            {$CFG->prefix}grade_items.grademax, {$CFG->prefix}grade_categories.id
            from {$CFG->prefix}grade_categories, {$CFG->prefix}grade_items
            where {$CFG->prefix}grade_categories.id = {$CFG->prefix}grade_items.iteminstance
            and {$CFG->prefix}grade_items.courseid = ? and itemtype='category';";

        $params = [$id];
        $cats = $DB->get_records_sql($query, $params);

        $data = [];
        foreach ($cats as $r) {
            $e['fullname'] = $r->fullname;
            $e['grademax'] = $r->grademax;
            $e['id'] = $r->id;

            $data[] = $e;
        }

        return $data;
    }

    /**
     * Get rubrics.
     *
     * @param mixed $gradeitemid Grade item id.
     * @return mixed The result of the operation.
     */
    public function get_rubrics($gradeitemid) {
        global $CFG, $DB;

        $conditions = ['id' => $gradeitemid];
        $gradeitem = $DB->get_record('grade_items', $conditions);

        $assigndata['assign_name'] = $gradeitem->itemname;
        $assigndata['definitions'] = [];

        $conditions = ['name' => $gradeitem->itemmodule];
        $module = $DB->get_record('modules', $conditions);
        if (!$module) {
            return $assigndata;
        }

        $conditions = ['course' => $gradeitem->courseid, 'module' => $module->id, 'instance' => $gradeitem->iteminstance];
        $cm = $DB->get_record('course_modules', $conditions);

        if (!$cm) {
            return $assigndata;
        }

        $context = context_module::instance($cm->id);

        $conditions = ['contextid' => $context->id];
        $area = $DB->get_record('grading_areas', $conditions);

        if (!$area) {
            return $assigndata;
        }

        $conditions = ['areaid' => $area->id];
        $definitions = $DB->get_records('grading_definitions', $conditions);

        $data = [];
        foreach ($definitions as $definition) {
            $d['definition'] = $definition->name;
            $conditions = ['definitionid' => $definition->id];
            $criteria = $DB->get_records('gradingform_rubric_criteria', $conditions);

            $d['criteria'] = [];
            foreach ($criteria as $c) {
                $datacriteria['description'] = $c->description;

                $conditions = ['criterionid' => $c->id];
                $levels = $DB->get_records('gradingform_rubric_levels', $conditions);

                $datalevels = [];
                foreach ($levels as $level) {
                    $dl['definition'] = $level->definition;
                    $dl['score'] = $level->score;

                    $datalevels[] = $dl;
                }
                $datacriteria['levels'] = $datalevels;

                $d['criteria'][] = $datacriteria;
            }

            $data[]  = $d;
        }

        $assigndata['definitions'] = $data;

        return $assigndata;
    }

    /**
     * User exists.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function user_exists($username) {

        global $CFG, $DB;
        $username = strtolower($username);
        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);
        if ($user) {
            return 1;
        }
        return 0;
    }

    /**
     * Get userinfo.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function get_userinfo($username) {
        global $DB;

        $username = strtolower($username);

        // Get user info from Joomla.
        $juserinfo = $this->call_method("getUserInfo", [
            'username' => $username,
            'app' => '',
        ]);

        return $juserinfo;
    }

    // Copy of create_user_record() in moodle/lib/moodlelib.php that removes password sync.
    // This used only when creating new accounts from Joomla.
    /**
     * Create joomdle user record.
     *
     * @param mixed $username Username.
     * @param mixed $password Password.
     * @param mixed $auth Auth.
     * @param mixed $userinfo Userinfo.
     * @return mixed The result of the operation.
     */
    public function create_joomdle_user_record($username, $password, $auth, &$userinfo) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');

        // Just in case check text case.
        $username = trim(core_text::strtolower($username));

        $authplugin = get_auth_plugin($auth);
        $customfields = $authplugin->get_custom_user_profile_fields();
        $newuser = new stdClass();
        if ($newinfo = $authplugin->get_userinfo($username)) {
            $newinfo = truncate_userinfo($newinfo);
            foreach ($newinfo as $key => $value) {
                if (in_array($key, $authplugin->userfields) || (in_array($key, $customfields))) {
                    $newuser->$key = $value;
                }
            }
        }

        if (!empty($newuser->email)) {
            if (email_is_not_allowed($newuser->email)) {
                unset($newuser->email);
            }
        }

        if (!isset($newuser->city)) {
            $newuser->city = '';
        }

        $newuser->auth = $auth;
        $newuser->username = $username;
        if ((is_array($newinfo)) && (array_key_exists('confirmed', $newinfo))) {
            $newuser->confirmed = $newinfo['confirmed'];
        }
        if ((is_array($newinfo)) && (array_key_exists('suspended', $newinfo))) {
            $newuser->suspended = $newinfo['suspended'];
        }

        // Fix for MDL-8480
        // user CFG lang for user if $newuser->lang is empty
        // or $user->lang is not an installed language.
        if (empty($newuser->lang) || !get_string_manager()->translation_exists($newuser->lang)) {
            $newuser->lang = $CFG->lang;
        }

        $newuser->lastip = getremoteaddr();
        $newuser->timecreated = time();
        $newuser->timemodified = $newuser->timecreated;
        $newuser->mnethostid = $CFG->mnet_localhost_id;

        $newuser->id = user_create_user($newuser, false, false);

        // Save user profile data.
        profile_save_data($newuser);

        // Trigger event.
        // The user-created event is intentionally not triggered here.

        return $newuser;
    }


    /**
     * Creates a new Joomdle user
     * Also used to update user profile if the user already exists
     *
     * @param string $username Joomla username
     */
    public function create_joomdle_user($username, $app = '') {
        global $CFG, $DB;

        $newinfo = [];

        $username = strtolower($username);
        // Crete new user if not exists.
        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);
        if (!$user) {
            $user = $this->create_joomdle_user_record($username, "", "joomdle", $newinfo);

            // Set first access as now.
            $conditions = ['id' => $user->id];
            $DB->set_field('user', 'firstaccess', time(), $conditions);
        } else if ($user->auth == 'joomdle') {
            $needsupdate = false;

            $updateuser = new stdClass();
            $updateuser->id = $user->id;

            // Update user info.
            if ($newinfo = $this->get_userinfo($username)) {
                $newinfo = truncate_userinfo($newinfo);

                $updatekeys = array_keys($newinfo);

                $authplugin = get_auth_plugin('joomdle');
                $customfields = $authplugin->get_custom_user_profile_fields();
                foreach ($updatekeys as $key) {
                    if (in_array($key, $authplugin->userfields) || (in_array($key, $customfields))) {
                        if (isset($newinfo[$key])) {
                            $value = $newinfo[$key];
                        } else {
                            $value = '';
                        }

                        if (isset($user->{$key}) && $user->{$key} != $value) { // Only update if it's changed.
                            // Don't update password, because we don't have it clear, and hash algo is different in Joomla.
                            if ($key == 'password') {
                                continue;
                            }
                            // Protect ID and auth method change.
                            if (($key == 'id') || ($key == 'auth')) {
                                continue;
                            }
                            $needsupdate = true;
                            $updateuser->$key = $value;
                        }
                    }
                }
            }
            if ($needsupdate) {
                require_once($CFG->dirroot . '/user/lib.php');
                user_update_user($updateuser, false, false);
            }
        }

        /* Get user pic */
        if ((array_key_exists('pic_url', $newinfo)) && ($newinfo['pic_url'])) {
            if ($newinfo['pic_url'] != 'none') {
                $joomlaurl = get_config('auth_joomdle', 'joomla_url');
                // Only add joomla_url if it is not a full URL already.
                if (strncmp($newinfo['pic_url'], 'http', 4) != 0) {
                    $picurl = $joomlaurl . '/' . $newinfo['pic_url'];
                } else {
                    $picurl = $newinfo['pic_url'];
                }

                $tmpfile = $this->download_avatar($picurl, $joomlaurl);
                if ($tmpfile !== false) {
                    try {
                        $user = get_complete_user_data('username', $username); // We need this to get user id.
                        $context = context_user::instance($user->id);
                        $rev = (int) process_new_icon($context, 'user', 'icon', 0, $tmpfile);

                        $conditions = ['id' => $user->id];
                        $DB->set_field('user', 'picture', $rev, $conditions);
                    } finally {
                        if (file_exists($tmpfile)) {
                            unlink($tmpfile);
                        }
                    }
                }
            }
        }

        /* Custom fields */
        if ($fields = $DB->get_records('user_info_field')) {
            foreach ($fields as $field) {
                if ((array_key_exists('cf_' . $field->id, $newinfo))) {
                    $data = new stdClass();
                    $data->fieldid = $field->id;
                    $data->data = $newinfo['cf_' . $field->id];
                    $data->userid = $user->id;

                    // Moodle does not accept null values here.
                    if ($data->data === null) {
                        continue;
                    }

                    /* update custom field */
                    if ($dataid = $DB->get_field('user_info_data', 'id', ['userid' => $user->id, 'fieldid' => $data->fieldid])) {
                        $data->id = $dataid;
                        $DB->update_record('user_info_data', $data);
                    } else {
                        $DB->insert_record('user_info_data', $data);
                    }
                }
            }
        }

        return 1;
    }

    /**
     * Enable user.
     *
     * @param mixed $username Username.
     * @param mixed $suspended Suspended.
     * @return mixed The result of the operation.
     */
    public function enable_user($username, $suspended = 0) {
        global $CFG, $DB;

        $user = get_complete_user_data('username', $username);

        if (!$user->id) {
            return;
        }

        $data = new stdClass();
        $data->id = $user->id;
        $data->suspended = $suspended;
        $DB->update_record('user', $data);
    }

    /**
     * Search courses.
     *
     * @param mixed $text Text.
     * @param mixed $phrase Phrase.
     * @param mixed $ordering Ordering.
     * @param mixed $limit Limit.
     * @param mixed $lang Lang.
     * @return mixed The result of the operation.
     */
    public function search_courses($text, $phrase, $ordering, $limit, $lang = 'en') {
        global $CFG, $DB, $SESSION;

        // Set the language.
        $SESSION->lang = $lang;

        $text = $text;
        switch ($phrase) {
            case 'exact':
                $text           = '%' . $text . '%';

                $likes = [];
                $like = $DB->sql_like('co.fullname', '?', false);
                $likes[] = $like;
                $params[] = $text;
                $like = $DB->sql_like('co.shortname', '?', false);
                $likes[] = $like;
                $params[] = $text;
                $like = $DB->sql_like('co.summary', '?', false);
                $likes[] = $like;
                $params[] = $text;

                $where          = '(' . implode(') OR (', $likes) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words = explode(' ', $text);
                $wheres = [];
                $wheres2 = [];

                $params = [];
                foreach ($words as $word) {
                    $likes = [];

                    $word           = '%' . $word . '%';
                    $like = $DB->sql_like('co.fullname', '?', false);
                    $likes[] = $like;
                    $params[] = $word;
                    $like = $DB->sql_like('co.shortname', '?', false);
                    $likes[] = $like;
                    $params[] = $word;
                    $like = $DB->sql_like('co.summary', '?', false);
                    $likes[] = $like;
                    $params[] = $word;

                    $where2 = '(' . implode(') OR (', $likes) . ')';
                    $wheres2[] = $where2;
                }
                $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres2) . ')';
                break;
        }

        $where = '(' . $where . ') AND ca.visible = 1 AND co.visible = 1';

        switch ($ordering) {
            case 'alpha':
                $order = 'co.fullname ASC';
                break;
            case 'category':
                $order = 'ca.name ASC, co.fullname ASC';
                break;
            case 'newest':
                $order = 'co.startdate DESC';
                break;
            case 'oldest':
                $order = 'co.startdate ASC';
                break;
            case 'popular':
            default:
                $order = 'co.fullname DESC';
        }

        $query = "SELECT
            co.id          AS remoteid,
            ca.id          AS cat_id,
            ca.name        AS cat_name,
            ca.description AS cat_description,
            co.sortorder,
            co.fullname,
            co.shortname,
            co.idnumber,
            co.summary,
            co.startdate
            FROM
            {$CFG->prefix}course_categories ca
            JOIN
            {$CFG->prefix}course co ON
            ca.id = co.category
            WHERE
            co.visible = '1' AND
            $where
            ORDER BY
            $order";

        $results = $DB->get_records_sql($query, $params, 0, $limit);
        $options['noclean'] = true;
        $data = [];

        foreach ($results as $r) {
            $c = get_object_vars($r);
            $c['fullname'] = format_string($c['fullname']);
            $c['summary'] = format_text($c['summary'], FORMAT_MOODLE, $options);
            $c['cat_name'] = format_string($c['cat_name']);
            $context = context_coursecat::instance($c['cat_id']);
            $c['cat_description'] = file_rewrite_pluginfile_urls(
                $c['cat_description'],
                'pluginfile.php',
                $context->id,
                'coursecat',
                'description',
                null
            );
            $c['cat_description'] = str_replace('pluginfile.php', '/auth/joomdle/pluginfile_joomdle.php', $c['cat_description']);
            $c['cat_description'] = format_text($c['cat_description'], FORMAT_MOODLE, $options);
            $data[] = $c;
        }

        return $data;
    }

    /**
     * Search categories.
     *
     * @param mixed $text Text.
     * @param mixed $phrase Phrase.
     * @param mixed $ordering Ordering.
     * @param mixed $limit Limit.
     * @param mixed $lang Lang.
     * @return mixed The result of the operation.
     */
    public function search_categories($text, $phrase, $ordering, $limit, $lang = 'en') {
        global $CFG, $DB, $SESSION;

        // Set the language.
        $SESSION->lang = $lang;

        $params = [];

        switch ($phrase) {
            case 'exact':
                $text           = '%' . $text . '%';

                $likes = [];
                $like = $DB->sql_like('ca.name', '?', false);
                $likes[] = $like;
                $params[] = $text;
                $like = $DB->sql_like('ca.description', '?', false);
                $likes[] = $like;
                $params[] = $text;

                $where          = '(' . implode(') OR (', $likes) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words = explode(' ', $text);
                $wheres = [];
                foreach ($words as $word) {
                    $word           = '%' . $word . '%';

                    $likes = [];

                    $like = $DB->sql_like('ca.name', '?', false);
                    $likes[] = $like;
                    $params[] = $word;
                    $like = $DB->sql_like('ca.description', '?', false);
                    $likes[] = $like;
                    $params[] = $word;

                    $where2 = '(' . implode(') OR (', $likes) . ')';
                    $wheres2[] = $where2;
                }

                $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres2) . ')';
                break;
        }
        $where = '(' . $where . ') AND ca.visible = 1';

        switch ($ordering) {
            case 'alpha':
            case 'category':
                $order = 'ca.name ASC';
                break;
            case 'newest':
            case 'oldest':
            case 'popular':
            default:
                $order = 'ca.name DESC';
        }

        $query = "SELECT
            ca.id          AS cat_id,
            ca.name        AS cat_name,
            ca.description AS cat_description
            FROM
            {$CFG->prefix}course_categories ca
            WHERE
            $where
            ORDER BY
            $order";

        $results = $DB->get_records_sql($query, $params, 0, $limit);

        $options['noclean'] = true;
        $data = [];
        foreach ($results as $r) {
            $c = get_object_vars($r);
            $c['cat_name'] = format_string($c['cat_name']);
            $c['cat_description'] = format_text($c['cat_description'], FORMAT_MOODLE, $options);
            $data[] = $c;
        }

        return $data;
    }

    /**
     * Search topics.
     *
     * @param mixed $text Text.
     * @param mixed $phrase Phrase.
     * @param mixed $ordering Ordering.
     * @param mixed $limit Limit.
     * @param mixed $lang Lang.
     * @return mixed The result of the operation.
     */
    public function search_topics($text, $phrase, $ordering, $limit = 50, $lang = 'en') {
        global $CFG, $DB, $SESSION;

        // Set the language.
        $SESSION->lang = $lang;

        switch ($phrase) {
            case 'exact':
                $text           = '%' . $text . '%';

                $likes = [];
                $like = $DB->sql_like('cs.summary', '?', false);
                $likes[] = $like;
                $params[] = $text;

                $like = $DB->sql_like('cs.name', '?', false);
                $likes[] = $like;
                $params[] = $text;

                $where          = '(' . implode(') OR (', $likes) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words = explode(' ', $text);
                foreach ($words as $word) {
                    $word           = '%' . $word . '%';

                    $likes = [];

                    $like = $DB->sql_like('cs.summary', '?', false);
                    $likes[] = $like;
                    $params[] = $word;

                    $like = $DB->sql_like('cs.name', '?', false);
                    $likes[] = $like;
                    $params[] = $word;

                    $where2 = '(' . implode(') OR (', $likes) . ')';
                    $wheres2[] = $where2;
                }
                $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres2) . ')';
                break;
        }
        $where = '(' . $where . ') AND cs.visible = 1 AND co.visible = 1';

        switch ($ordering) {
            case 'alpha':
                $order = 'cs.summary ASC';
                break;
            case 'category':
                $order = 'co.id ASC';
                break;
            case 'newest':
                $order = 'co.id ASC, cs.section DESC';
                break;
            case 'oldest':
                $order = 'co.id ASC, cs.section ASC';
                break;
            case 'popular':
            default:
                $order = 'cs.summary DESC';
        }

        $query = "SELECT cs.id, cs.name as sec_name,
            co.id AS remoteid,
            co.fullname,
            cs.course,
            cs.section,
            cs.summary,
            ca.id as cat_id,
            ca.name as cat_name
            FROM
            {$CFG->prefix}course_sections cs
            JOIN {$CFG->prefix}course co  ON
            co.id = cs.course
            LEFT JOIN {$CFG->prefix}course_categories ca  ON
            ca.id = co.category
            WHERE
            $where
            ORDER BY
            $order";

        $results = $DB->get_records_sql($query, $params, 0, $limit);

        $options['noclean'] = true;
        $data = [];
        foreach ($results as $r) {
            $c = get_object_vars($r);
            $c['fullname'] = format_string($c['fullname']);
            $c['summary'] = format_text($c['summary'], FORMAT_MOODLE, $options);
            $c['cat_name'] = format_string($c['cat_name']);
            if ($c['sec_name']) {
                $c['sec_name'] = format_string($c['sec_name']);
            } else {
                $c['sec_name'] = get_string('topic') . ' ' . $c['section'];
            }
            $data[] = $c;
        }

        return $data;
    }

    /**
     * Multiple enrol to course and group.
     *
     * @param mixed $username Username.
     * @param mixed $courses Courses.
     * @param mixed $roleid Roleid.
     * @return int The result of the operation.
     */
    public function multiple_enrol_to_course_and_group($username, $courses, $roleid = 0) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        foreach ($courses as $course) {
            $conditions = ['id' => $course['id']];
            $coursedb = $DB->get_record('course', $conditions);

            if (!$coursedb) {
                continue;
            }

            $this->enrol_user($username, $coursedb->id, $roleid);

            // Group.
            groups_add_member($course['group_id'], $user->id);
        }

        return 1;
    }

    /**
     * Get course groups.
     *
     * @param mixed $id Id.
     * @return mixed The result of the operation.
     */
    public function get_course_groups($id) {
        $groups = groups_get_all_groups($id);

        $rdo = [];

        foreach ($groups as $group) {
            $g['id'] = $group->id;
            $g['name'] = $group->name;
            $g['description'] = $group->description;

            $rdo[] = $g;
        }

        return $rdo;
    }

    /**
     * Get group members.
     *
     * @param mixed $groupid Group id.
     * @param mixed $search Search.
     * @return mixed The result of the operation.
     */
    public function get_group_members($groupid, $search = '') {
        $users = groups_get_members($groupid);

        $rdo = [];
        foreach ($users as $u) {
            if ($search) {
                if (
                    (stripos($u->username, $search) === false)
                    && (stripos($u->firstname, $search) === false)
                    && (stripos($u->lastname, $search) === false)
                    && (stripos($u->idnumber, $search) === false)
                ) {
                    continue;
                }
            }

            $member['id'] = $u->id;
            $member['firstname'] = $u->firstname;
            $member['lastname'] = $u->lastname;
            $member['username'] = $u->username;

            $rdo[] = $member;
        }

        return $rdo;
    }

    /**
     * Get courses and groups.
     * @return mixed The result of the operation.
     */
    public function get_courses_and_groups() {
        $courses = $this->list_courses();

        $c = [];
        foreach ($courses as $course) {
            $coursedata['remoteid'] = $course['remoteid'];
            $coursedata['fullname'] = $course['fullname'];

            $coursedata['groups'] = $this->get_course_groups($course['remoteid']);

            $c[] = $coursedata;
        }

        return $c;
    }


    /**
     * Multiple enrol.
     *
     * @param mixed $username Username.
     * @param mixed $courses Courses.
     * @param mixed $roleid Roleid.
     * @return mixed The result of the operation.
     */
    public function multiple_enrol($username, $courses, $roleid = self::ROLE_STUDENT) {
        global $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            $this->create_joomdle_user($username);
        }
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return;
        }

        foreach ($courses as $c) {
            $conditions = ['id' => $c['id']];
            $course = $DB->get_record('course', $conditions);

            if (!$course) {
                continue;
            }

            $this->enrol_user($username, $course->id, $roleid);
        }

        return 0;
    }

    /**
     * Enrol user.
     *
     * @param mixed $username Username.
     * @param mixed $courseid Course id.
     * @param mixed $roleid Roleid.
     * @param mixed $timestart Timestart.
     * @param mixed $timeend Timeend.
     * @return mixed The result of the operation.
     */
    public function enrol_user($username, $courseid, $roleid = self::ROLE_STUDENT, $timestart = 0, $timeend = 0) {
        global $CFG, $DB, $PAGE, $USER;

        $username = strtolower($username);
        /* Create the user before if it is not created yet */
        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);
        if (!$user) {
            $this->create_joomdle_user($username);
        }

        $user = $DB->get_record('user', $conditions);
        $conditions = ['id' => $courseid];
        $course = $DB->get_record('course', $conditions);

        if (!$course) {
            return 0;
        }

        // Get enrol start and end dates of manual enrolment plugin.
        $manager = new course_enrolment_manager($PAGE, $course);

        $instances = $manager->get_enrolment_instances();
        $plugins = $manager->get_enrolment_plugins();

        if (!$timestart) {
            // Set NOW as enrol start if not one defined.
            $today = time();
            $today = make_timestamp(
                date('Y', $today),
                date('m', $today),
                date('d', $today),
                date('H', $today),
                date('i', $today),
                date('s', $today)
            );
            $timestart = $today;
        }

        $found = false;
        foreach ($instances as $instance) {
            if ($instance->enrol == 'manual') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return 0;
        }

        // Use the default role configured for manual enrolment.
        if (!$roleid) {
            $roleid = $instance->roleid;
        }

        $plugin = $plugins['manual'];

        if ($instance->enrolperiod != 0) {
            $timeend   = $timestart + $instance->enrolperiod;
        }

        // First, check if user is already enroled but suspended, so we just need to enable it.
        $conditions = ['courseid' => $courseid, 'enrol' => 'manual'];
        $enrol = $DB->get_record('enrol', $conditions);

        if (!$enrol) {
            return 0;
        }

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        $conditions = ['enrolid' => $enrol->id, 'userid' => $user->id];
        $ue = $DB->get_record('user_enrolments', $conditions);

        if ($ue) {
            // User already enroled.
            // Can be suspended, or maybe enrol time passed.
            // Just activate enrolment and set new dates.
            $ue->status = 0; // Active.
            $ue->timestart = $timestart;
            $ue->timeend = $timeend;
            $ue->timemodified = $timestart;
            $DB->update_record('user_enrolments', $ue);

            // Update or insert role if needed.
            $context = context_course::instance($courseid);
            $conditions = ['contextid' => $context->id, 'userid' => $user->id, 'roleid' => $roleid];
            $ra = $DB->get_record('role_assignments', $conditions);

            if (!$ra) {
                // Insert new row.
                $ra = new stdClass();
                $ra->roleid = $roleid;
                $ra->contextid = $context->id;
                $ra->userid = $user->id;
                $ra->timemodified = time();
                $ra->modifierid = $USER->id;
                $DB->insert_record("role_assignments", $ra);
            } else {
                // Update row.
                $ra->roleid = $roleid;
                $DB->update_record('role_assignments', $ra);
            }

            return 1;
        }

        $plugin->enrol_user($instance, $user->id, $roleid, $timestart, $timeend);

        return 1;
    }

    /**
     * Get cat name.
     *
     * @param mixed $catid Cat id.
     * @return mixed The result of the operation.
     */
    public function get_cat_name($catid) {
        global $CFG, $DB;

        $catid = addslashes($catid);

        $query = "SELECT name
            FROM  {$CFG->prefix}course_categories
            WHERE id = ?;";

        $params = [$catid];
        $rdo = $DB->get_records_sql($query, $params);
        $row = (reset($rdo));
        return format_string($row->name);
    }

    /**
     * Get moodle users.
     *
     * @param mixed $limitstart Limitstart.
     * @param mixed $limit Limit.
     * @param mixed $order Order.
     * @param mixed $orderdir Order dir.
     * @param mixed $search Search.
     * @return mixed The result of the operation.
     */
    public function get_moodle_users($limitstart, $limit, $order, $orderdir, $search) {
        global $CFG, $DB;

        $admins = get_admins();
        foreach ($admins as $admin) {
            $a[] = $admin->id;
        }
        $a[] = 1; // Guest user.

        $allowedorders = [
            'id' => 'id',
            'username' => 'username',
            'email' => 'email',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'auth' => 'auth',
            'name' => 'firstname, lastname',
        ];

        if (in_array($order, $allowedorders)) {
            $order = $allowedorders[$order];
        } else {
            $order = 'firstname, lastname';
        }

        $orderdir = strtoupper($orderdir) === 'DESC' ? 'DESC' : 'ASC';

        if ($order != "") {
            $orderc = "  ORDER BY $order $orderdir";
        } else {
            $orderc = "";
        }

        $limitstart = max(0, (int)$limitstart);
        $limit = max(0, (int)$limit);

        if ($search) {
            $params = [];
            $likeu = $DB->sql_like('username', '?', false);
            $params[] = "%$search%";
            $likee = $DB->sql_like('email', '?', false);
            $params[] = "%$search%";
            $fullname = $DB->sql_concat('firstname', "' '", 'lastname');
            $likel = $DB->sql_like($fullname, '?', false);
            $params[] = "%$search%";

            $users = $DB->get_records_sql("SELECT id, username, email,  firstname, lastname ,auth
                        FROM {$CFG->prefix}user
                        WHERE deleted = 0
                        AND(({$likeu}) OR ({$likee}) OR ({$likel}))
                        $orderc
                        ", $params, $limitstart, $limit);
        } else {
            $users = $DB->get_records_sql("SELECT id, username, email,  firstname, lastname ,auth
                    FROM {$CFG->prefix}user
                    WHERE deleted = 0
                    $orderc
                    ", [], $limitstart, $limit);
        }

        $i = 0;
        $u = [];
        foreach ($users as $user) {
            $u[$i] = get_object_vars($user);
            if (in_array($user->id, $a)) {
                $u[$i]['admin'] = '1';
            } else {
                $u[$i]['admin'] = '0';
            }

            $u[$i]['name'] = $user->firstname . ' ' . $user->lastname;

            $i++;
        }
        return $u;
    }

    /**
     * Get moodle users number.
     *
     * @param mixed $search Search.
     * @return mixed The result of the operation.
     */
    public function get_moodle_users_number($search = "") {
        global $CFG, $DB;

        $search = trim($search);
        $admins = get_admins();
        foreach ($admins as $admin) {
            $a[] = $admin->id;
        }
        $a[] = 1; // Guest user.
        $userlist = "'" . implode("','", $a) . "'";

        if ($search) {
            $params = [];
            $likeu = $DB->sql_like('username', '?', false);
            $params[] = "%$search%";
            $likee = $DB->sql_like('email', '?', false);
            $params[] = "%$search%";
            $fullname = $DB->sql_concat('firstname', "' '", 'lastname');
            $likel = $DB->sql_like($fullname, '?', false);
            $params[] = "%$search%";

            $users = $DB->count_records_sql("SELECT count(id) as n
                            FROM {$CFG->prefix}user
                            WHERE deleted = 0
                           AND (({$likeu}) OR ({$likee}) OR ({$likel}))", $params);
        } else {
            $users = $DB->count_records_sql("SELECT count(id) as n
                    FROM {$CFG->prefix}user
                    WHERE deleted = 0");
        }

        return $users;
    }

    /**
     * Get moodle only users.
     *
     * @param mixed $users Users.
     * @param mixed $search Search.
     * @return mixed The result of the operation.
     */
    public function get_moodle_only_users($users, $search) {
        global $CFG, $DB;

        /* Don't show admins and guets */
        $admins = get_admins();
        $a = [];
        foreach ($admins as $admin) {
            $a[] = $admin->id;
        }
        $a[] = 1; // Guest user.
        $adminlist = "'" . implode("','", $a) . "'";

        $usernames = ['this_is_a_kludge_to_avoid_empty_array'];
        foreach ($users as $user) {
            $username = strtolower($user['username']);
            $usernames[] = $username;
        }

        [$notinsql, $params] = $DB->get_in_or_equal($usernames, SQL_PARAMS_QM, 'param', false);

        $users = [];
        if ($search) {
            $likeu = $DB->sql_like('username', '?', false);
            $params[] = "%$search%";
            $likee = $DB->sql_like('email', '?', false);
            $params[] = "%$search%";
            $fullname = $DB->sql_concat('firstname', "' '", 'lastname');
            $likel = $DB->sql_like($fullname, '?', false);
            $params[] = "%$search%";

            $users = $DB->get_records_sql("SELECT id, username, email,  firstname, lastname, auth
                        FROM {$CFG->prefix}user
                        WHERE deleted = 0
                        AND auth != 'webservice'
                        AND (username $notinsql)
                        AND(({$likeu}) OR ({$likee}) OR ({$likel}))", $params);
        } else {
            $users = $DB->get_records_sql("SELECT id, username, email, firstname, lastname, auth
                    FROM {$CFG->prefix}user
                    WHERE deleted = 0
                    AND auth != 'webservice'
                    AND (username $notinsql)", $params);
        }

        $n = count($users);
        $i = 0;
        $u = [];
        foreach ($users as $user) {
            $u[$i] = get_object_vars($user);
            if (in_array($user->id, $a)) {
                $u[$i]['admin'] = '1';
            } else {
                $u[$i]['admin'] = '0';
            }

            $u[$i]['name'] = $user->firstname . ' ' . $user->lastname;

            $i++;
        }

        return $u;
    }

    /**
     * Delete user.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function delete_user($username) {
        global $DB;

        $username = strtolower($username);
        $conditions = ["username" => $username];
        $user = $DB->get_record("user", $conditions);

        if ($user) {
            delete_user($user);
            return 1;
        }
        return 0;
    }

    /**
     * User id.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function user_id($username) {
        global $DB;

        $username = strtolower($username);
        $conditions = ["username" => $username];
        $user = $DB->get_record("user", $conditions);

        if (!$user) {
            return 0;
        }

        return $user->id;
    }

    /**
     * User details.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function user_details($username) {
        global $DB, $CFG;

        $username = strtolower($username);
        $conditions = ["username" => $username];
        $user = $DB->get_record("user", $conditions);

        $u['username'] = $user->username;
        $u['firstname'] = $user->firstname;
        $u['lastname'] = $user->lastname;
        $u['email'] = $user->email;
        $u['id'] = $user->id;
        $u['name'] = $user->firstname . " " . $user->lastname;
        $u['city'] = $user->city;
        $u['country'] = $user->country;
        $u['lang'] = $user->lang;
        $u['timezone'] = $user->timezone;
        $u['phone1'] = $user->phone1;
        $u['phone2'] = $user->phone2;
        $u['address'] = $user->address;
        $u['description'] = $user->description;
        $u['institution'] = $user->institution;
        $u['idnumber'] = $user->idnumber;
        $u['department'] = $user->department;
        $u['picture'] = $user->picture;
        $u['lastnamephonetic'] = $user->lastnamephonetic;
        $u['firstnamephonetic'] = $user->firstnamephonetic;
        $u['middlename'] = $user->middlename;
        $u['alternatename'] = $user->alternatename;

        $id = $user->id;
        $usercontext = context_user::instance($id);
        $contextid = $usercontext->id;

        if ($user->picture) {
            $u['pic_url'] = $CFG->wwwroot . "/pluginfile.php/$contextid/user/icon/f1";
        }

        /* Custom fields */
        $query = "SELECT f.id, d.data
            FROM {$CFG->prefix}user_info_field as f, {$CFG->prefix}user_info_data d
            WHERE f.id=d.fieldid and userid = ?";

        $params = [$id];
        $records = $DB->get_records_sql($query, $params);

        $i = 0;
        $u['custom_fields'] = [];
        foreach ($records as $field) {
            $u['custom_fields'][$i]['id'] = $field->id;
            $u['custom_fields'][$i]['data'] = $field->data;
            $i++;
        }

        return $u;
    }

    /**
     * User custom fields.
     * @return mixed The result of the operation.
     */
    public function user_custom_fields() {
        global $DB, $CFG;

        $query = "SELECT id, name, shortname
                    FROM {$CFG->prefix}user_info_field";

        $records = $DB->get_records_sql($query);
        $i = 0;
        $customfields = [];
        foreach ($records as $field) {
            $customfields[$i]['id'] = $field->id;
            $customfields[$i]['name'] = $field->name;
            $customfields[$i]['shortname'] = $field->shortname;
            $i++;
        }

        return $customfields;
    }

    /**
     * User details by id.
     *
     * @param mixed $id Id.
     * @return mixed The result of the operation.
     */
    public function user_details_by_id($id) {
        global $DB;

        $conditions = ["id" => $id];
        $user = $DB->get_record("user", $conditions);

        $u['username'] = $user->username;

        return $u;
    }

    /**
     * Update session.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function update_session($username) {
        global $DB, $CFG;

        $conditions = ["username" => $username];
        $user = $DB->get_record("user", $conditions);

        if (!$user) {
            return false;
        }

        $params = [$user->id];
        $sql = "SELECT sid FROM {$CFG->prefix}sessions " .
            " WHERE userid = ? " .
            " ORDER BY timemodified DESC LIMIT 1";

        $session = $DB->get_records_sql($sql, $params);

        if (!$session) {
            return false;
        }

        $sessionobj = array_shift($session);

        $conditions = ['sid' => $sessionobj->sid];
        $session = $DB->get_record('sessions', $conditions);

        if (!$session) {
            return false;
        }

        $session->timemodified = time();
        $DB->update_record('sessions', $session);

        return true;
    }

    /**
     * Migrate to joomdle.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function migrate_to_joomdle($username) {
        global $DB;
        $username = strtolower($username);
        $conditions = ['username' => $username];
        $DB->set_field('user', 'auth', 'joomdle', $conditions);

        return true;
    }

    // Get user events.
    // Used by calendar module.
    /**
     * My events.
     *
     * @param mixed $username Username.
     * @param mixed $cursosid Cursosid.
     * @return mixed The result of the operation.
     */
    public function my_events($username, $cursosid) {
        global $CFG, $DB;

        $username = strtolower($username);
        $whereclause = '';
        $params = [];
        if ($username == 'admin') {
            $whereclause .= ' (groupid = 0 AND courseid = 1) ';
        } else {
            $user = get_complete_user_data('username', $username);

            if (!$user) {
                return [];
            }

            $w = [];
            $params1 = [];
            $params2 = [];
            $cursosids = [];
            foreach ($cursosid as $course) {
                $courseid = $course['id'];
                $cursosids[] = $courseid;
                // FIXME: groups not working.
            }

            $whereclause = ' (userid = ? AND courseid = 0 AND groupid = 0)';
            $params1[] = $user->id;

            [$insql, $params2] = $DB->get_in_or_equal($cursosids);

            $params = array_merge($params1, $params2);

            foreach ($w as $cond) {
                $whereclause .= $cond;
            }

            $whereclause .= " OR  (groupid = 0 AND courseid $insql) ";
        }
        $whereclause .= ' AND visible = 1';
        // Query debugging can be enabled here when needed.
        $events = $DB->get_records_select('event', $whereclause, $params);

        $data = [];
        foreach ($events as $event) {
            $e['name'] = $event->name;
            $e['timestart'] = $event->timestart;
            $e['courseid'] = $event->courseid;

            $data[] = $e;
        }

        return $data;
    }

    /**
     * Get events.
     *
     * @param mixed $username Username.
     * @param mixed $startdate Start date.
     * @param mixed $enddate End date.
     * @param mixed $type Type.
     * @param mixed $courseid Course id.
     * @return mixed The result of the operation.
     */
    public function get_events($username, $startdate, $enddate, $type, $courseid) {
        global $USER, $DB;

        $username = strtolower($username);
        $user = get_complete_user_data('username', $username);

        if ($username != 'guest') {
            if ($courseid) {
                if (! $course = $DB->get_record("course", ["id" => $courseid])) {
                    return [];
                }
                $coursestoload = [$courseid => $course];
            } else {
                $coursestoload = enrol_get_users_courses($user->id, true);
            }
        } else {
            if (! $course = $DB->get_record("course", ["id" => 1])) {
                return [];
            }
            $coursestoload = [1 => $course];
        }

        // Save $USER var to reset it after use. It holds web service user. Probably not needed, but just in case...
        $wsuser = $USER;
        $USER = $user;
        $ignorefilters = false;
        [$courses, $group, $useridnotused] = calendar_set_filters($coursestoload, $ignorefilters);
        $USER = $wsuser; // Reset the global user.

        if (!$enddate) {
            $enddate = PHP_INT_MAX;
        }

        $events = calendar_get_events($startdate, $enddate, $user->id, $group, $courses);

        $es = [];
        foreach ($events as $event) {
            // We filter user and site events here.
            if (($type == 'site') &&  ($event->eventtype != 'site')) {
                continue;
            } else if ($type == 'user') {
                // We only show events with userid set as the user.
                if ($event->userid != $user->id) {
                    continue;
                }
            }

            $e = [];
            $e['id'] = $event->id;
            $e['name'] = $event->name;
            $e['description'] = $event->description;
            $e['timestart'] = $event->timestart;
            $e['timeduration'] = $event->timeduration;

            $es[] = $e;
        }

        return $es;
    }

    /**
     * Get event.
     *
     * @param mixed $id Id.
     * @return mixed The result of the operation.
     */
    public function get_event($id) {
        global $DB;

        $conditions = ['id' => $id];
        $event = $DB->get_record('event', $conditions);

        $e = [];
        $e['id'] = $event->id;
        $e['name'] = $event->name;
        $e['description'] = $event->description;
        $e['timestart'] = $event->timestart;
        $e['timeduration'] = $event->timeduration;
        $e['type'] = $event->eventtype;

        return $e;
    }

    /**
     * Course enrol methods.
     *
     * @param mixed $courseid Course id.
     * @return mixed The result of the operation.
     */
    public function course_enrol_methods($courseid) {
        $instances = enrol_get_instances($courseid, true);

        $i = 0;
        $m = [];
        foreach ($instances as $method) {
            $m[$i]['id'] = $method->id;
            $m[$i]['enrol'] = $method->enrol;
            $m[$i]['enrolstartdate'] = $method->enrolstartdate;
            $m[$i]['enrolenddate'] = $method->enrolenddate;
            $i++;
        }

        return $m;
    }

    /**
     * Multiple suspend enrolment.
     *
     * @param mixed $username Username.
     * @param mixed $courses Courses.
     * @return mixed The result of the operation.
     */
    public function multiple_suspend_enrolment($username, $courses) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return;
        }

        foreach ($courses as $c) {
            $conditions = ['id' => $c['id']];
            $course = $DB->get_record('course', $conditions);

            if (!$course) {
                continue;
            }

            $this->suspend_enrolment($username, $course->id);
        }

        return 0;
    }

    /**
     * Suspend enrolment.
     *
     * @param mixed $username Username.
     * @param mixed $courseid Course id.
     * @return int The result of the operation.
     */
    public function suspend_enrolment($username, $courseid) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['courseid' => $courseid, 'enrol' => 'manual'];
        $enrol = $DB->get_record('enrol', $conditions);

        if (!$enrol) {
            return 0;
        }

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        $conditions = ['enrolid' => $enrol->id, 'userid' => $user->id];
        $ue = $DB->get_record('user_enrolments', $conditions);

        if (!$ue) {
            return 0;
        }

        $ue->status = 1; // Suspended.
        $DB->update_record('user_enrolments', $ue);

        return 1;
    }


    /**
     * Multiple unenrol user.
     *
     * @param mixed $username Username.
     * @param mixed $courses Courses.
     * @return mixed The result of the operation.
     */
    public function multiple_unenrol_user($username, $courses) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return;
        }

        foreach ($courses as $c) {
            $conditions = ['id' => $c['id']];
            $course = $DB->get_record('course', $conditions);

            if (!$course) {
                continue;
            }

            $this->unenrol_user($username, $course->id);
        }

        return 0;
    }

    // Unenrol user totally.
    /**
     * Unenrol user.
     *
     * @param mixed $username Username.
     * @param mixed $courseid Course id.
     * @return int The result of the operation.
     */
    public function unenrol_user($username, $courseid) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['courseid' => $courseid, 'enrol' => 'manual'];
        $enrol = $DB->get_record('enrol', $conditions);

        if (!$enrol) {
            return 0;
        }

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        $conditions = ['enrolid' => $enrol->id, 'userid' => $user->id];
        $ue = $DB->get_record('user_enrolments', $conditions);

        if (!$ue) {
            // If we cant find a manual enrolemnt, see if we have a self one.
            $conditions = ['courseid' => $courseid, 'enrol' => 'self'];
            $enrol = $DB->get_record('enrol', $conditions);

            if (!$enrol) {
                return 0;
            }

            $conditions = ['enrolid' => $enrol->id, 'userid' => $user->id];
            $ue = $DB->get_record('user_enrolments', $conditions);

            if (!$ue) {
                return 0;
            }
        }

        $instance = $DB->get_record('enrol', ['id' => $ue->enrolid], '*', MUST_EXIST);

        $plugin = enrol_get_plugin($instance->enrol);

        $plugin->unenrol_user($instance, $ue->userid);

        return 1;
    }

    /**
     * Multiple remove from group.
     *
     * @param mixed $username Username.
     * @param mixed $courses Courses.
     * @return mixed The result of the operation.
     */
    public function multiple_remove_from_group($username, $courses) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return;
        }

        foreach ($courses as $c) {
            $conditions = ['id' => $c['id']];
            $course = $DB->get_record('course', $conditions);

            if (!$course) {
                continue;
            }

            // Group.
            groups_remove_member($c['group_id'], $user->id);
        }

        return 0;
    }

    /**
     * My certificates.
     *
     * @param mixed $username Username.
     * @param mixed $type Type.
     * @return mixed The result of the operation.
     */
    public function my_certificates($username, $type = 'normal') {
        switch ($type) {
            case "normal":
                return $this->my_certificates_normal($username);
                break;
            case "simple":
                return $this->my_certificates_simple($username);
                break;
            case "custom":
                return $this->my_certificates_custom($username);
                break;
            case "coursecertificate":
                return $this->my_certificates_coursecertificate($username);
                break;
        }
    }

    /**
     * My certificates normal.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    private function my_certificates_normal($username) {
        global $CFG, $DB;

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        $userid = $user->id;

        $cursos = enrol_get_users_courses($user->id, true);

        if (!count($cursos)) {
            return [];
        }

        $ids = [];
        foreach ($cursos as $course) {
            $ids[] = $course->id;
        }
        [$insql, $params2] = $DB->get_in_or_equal($ids);

        $params1 = [$userid];
        $params = array_merge($params1, $params2);

        $certs = $DB->get_records_sql("SELECT  c.name, c.id, ci.timecreated as certdate
                FROM {$CFG->prefix}certificate c
                LEFT JOIN {$CFG->prefix}certificate_issues ci ON c.id = ci.certificateid
                WHERE ci.userid = ?
                AND c.course  $insql
                ORDER BY ci.timecreated DESC", $params);

        $c = [];
        foreach ($certs as $cert) {
            $coursemodule = get_coursemodule_from_instance("certificate", $cert->id);
            $certificate['id'] = $coursemodule->id;
            $certificate['name']  = $cert->name;
            $certificate['date']  = $cert->certdate;

            $c[] = $certificate;
        }

        return $c;
    }

    /**
     * My certificates simple.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    private function my_certificates_simple($username) {
        global $CFG, $DB;

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);
        $userid = $user->id;

        $cursos = enrol_get_users_courses($user->id, true);

        if (!count($cursos)) {
            return [];
        }

        $ids = [];
        foreach ($cursos as $course) {
            $ids[] = $course->id;
        }
        [$insql, $params2] = $DB->get_in_or_equal($ids);

        $params1 = [$userid];
        $params = array_merge($params1, $params2);

        $certs = $DB->get_records_sql("SELECT  c.name, c.id, ci.timecreated as certdate
                FROM {$CFG->prefix}simplecertificate c
                LEFT JOIN {$CFG->prefix}simplecertificate_issues ci ON c.id = ci.certificateid
                WHERE ci.userid = ?
                AND c.course $insql
                ORDER BY ci.timecreated DESC", $params);

        $c = [];
        foreach ($certs as $cert) {
            $coursemodule = get_coursemodule_from_instance("simplecertificate", $cert->id);
            $certificate['id'] = $coursemodule->id;
            $certificate['name'] = $cert->name;
            $certificate['date'] = $cert->certdate;

            $c[] = $certificate;
        }

        return $c;
    }

    /**
     * My certificates custom.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    private function my_certificates_custom($username) {
        global $CFG, $DB;

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        $userid = $user->id;

        $cursos = enrol_get_users_courses($user->id, true);

        if (!count($cursos)) {
            return [];
        }

        $ids = [];
        foreach ($cursos as $course) {
            $ids[] = $course->id;
        }
        [$insql, $params2] = $DB->get_in_or_equal($ids);

        $params1 = [$userid];
        $params = array_merge($params1, $params2);

        $certs = $DB->get_records_sql("SELECT  c.name, c.id, ci.timecreated as certdate
                FROM {$CFG->prefix}customcert c
                LEFT JOIN {$CFG->prefix}customcert_issues ci ON c.id = ci.customcertid
                WHERE ci.userid = ?
                AND c.course $insql
                ORDER BY ci.timecreated DESC", $params);

        $c = [];
        foreach ($certs as $cert) {
            $coursemodule = get_coursemodule_from_instance("customcert", $cert->id);
            $certificate['id'] = $coursemodule->id;
            $certificate['name'] = $cert->name;
            $certificate['date'] = $cert->certdate;

            $c[] = $certificate;
        }

        return $c;
    }

    /**
     * My certificates coursecertificate.
     *
     * @param mixed $username Username.
     * @return mixed The result of the operation.
     */
    public function my_certificates_coursecertificate($username) {
        global $CFG, $DB;

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        $userid = $user->id;

        $params = [$userid];
        $certs = $DB->get_records_sql("SELECT c.id, ci.timecreated,c.name, ci.code
            FROM {$CFG->prefix}tool_certificate_issues ci
            LEFT JOIN {$CFG->prefix}coursecertificate c ON c.course = ci.courseid
            WHERE ci.userid = ?", $params);

        $c = [];
        foreach ($certs as $cert) {
            $coursemodule = get_coursemodule_from_instance("coursecertificate", $cert->id);
            $certificate['id'] = $coursemodule->id;
            $certificate['name'] = $cert->name;
            $certificate['date'] = $cert->timecreated;
            $certificate['code'] = $cert->code;

            $c[] = $certificate;
        }

        return $c;
    }

    /**
     * Get users certificates.
     *
     * @param mixed $users Users.
     * @param mixed $type Type.
     * @return mixed The result of the operation.
     */
    public function get_users_certificates($users, $type = 'normal') {
        $certs = [];
        foreach ($users as $user) {
            $c = $this->my_certificates($user['username'], $type);
            $user['certificates'] = $c;
            $certs[] = $user;
        }
        return $certs;
    }

    /**
     * Add cohort member.
     *
     * @param mixed $username Username.
     * @param mixed $cohortid Cohort id.
     * @return mixed The result of the operation.
     */
    public function add_cohort_member($username, $cohortid) {
        global $CFG, $DB;

        $username = strtolower($username);
        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        $conditions = ['userid' => $user->id, 'cohortid' => $cohortid];
        ;
        $member = $DB->get_record('cohort_members', $conditions);

        if ($member) {
            return 0;
        }

        cohort_add_member($cohortid, $user->id);

        return 1;
    }

    /**
     * Remove cohort member.
     *
     * @param mixed $username Username.
     * @param mixed $cohortid Cohort id.
     * @return mixed The result of the operation.
     */
    public function remove_cohort_member($username, $cohortid) {
        global $CFG, $DB;

        $username = strtolower($username);
        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return 0;
        }

        $conditions = ['userid' => $user->id, 'cohortid' => $cohortid];
        $member = $DB->get_record('cohort_members', $conditions);

        if (!$member) {
            return 0;
        }

        cohort_remove_member($cohortid, $user->id);

        return 1;
    }

    /**
     * Multiple add cohort member.
     *
     * @param mixed $username Username.
     * @param mixed $cohorts Cohorts.
     * @return mixed The result of the operation.
     */
    public function multiple_add_cohort_member($username, $cohorts) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return;
        }

        foreach ($cohorts as $cohort) {
            $this->add_cohort_member($username, $cohort['id']);
        }

        return 0;
    }

    /**
     * Multiple remove cohort member.
     *
     * @param mixed $username Username.
     * @param mixed $cohorts Cohorts.
     * @return mixed The result of the operation.
     */
    public function multiple_remove_cohort_member($username, $cohorts) {
        global $CFG, $DB;

        $username = strtolower($username);

        $conditions = ['username' => $username];
        $user = $DB->get_record('user', $conditions);

        if (!$user) {
            return;
        }

        foreach ($cohorts as $cohort) {
            $this->remove_cohort_member($username, $cohort['id']);
        }

        return 0;
    }

    /**
     * Get cohorts.
     * @return mixed The result of the operation.
     */
    public function get_cohorts() {
        global $CFG, $DB;

        $query = "SELECT id, name
          FROM {$CFG->prefix}cohort";

        $cohorts = $DB->get_records_sql($query);

        $rdo = [];
        foreach ($cohorts as $cohort) {
            $c['id'] = $cohort->id;
            $c['name'] = $cohort->name;

            $rdo[] = $c;
        }

        return $rdo;
    }

    /**
     * Get themes.
     * @return mixed The result of the operation.
     */
    public function get_themes() {
        $availablethemes = core_component::get_plugin_list('theme');

        $themes = [];
        foreach ($availablethemes as $name => $path) {
            $theme = [];
            $theme['name'] = $name;
            $themes[] = $theme;
        }

        return $themes;
    }

    /**
     * Get course users.
     *
     * @param mixed $courseid Course id.
     * @return mixed The result of the operation.
     */
    public function get_course_users($courseid) {
        global $CFG, $DB;

        $context = context_course::instance($courseid);
        $users = get_enrolled_users($context, '', 0, 'u.username');

        $roles = $DB->get_records_sql("SELECT id, name, shortname
            FROM {$CFG->prefix}role");

        $rolenames = role_fix_names($roles, null, ROLENAME_BOTH, true);

        $courseroles = [];
        foreach ($roles as $role) {
            // Only return roles assignables in course context.
            $contextlevels = get_role_contextlevels($role->id);
            if (!in_array(CONTEXT_COURSE, $contextlevels)) {
                continue;
            }

            $r = [];
            $r['id'] = $role->id;
            $r['name'] = $rolenames[$role->id];

            $courseroles[] = $r;
        }

        foreach ($courseroles as $role) {
            $roleusers = get_role_users($role['id'], $context);

            foreach ($roleusers as $user) {
                if (array_key_exists($user->username, $users)) {
                    if (!property_exists($users[$user->username], 'roles')) {
                        $users[$user->username]->roles = [];
                    }
                    $users[$user->username]->roles[] = $role;
                }
            }
        }

        return $users;
    }

    /**
     * Logoutpage hook.
     * @return mixed The result of the operation.
     */
    public function logoutpage_hook() {
        global $redirect, $USER;

        if ($USER->auth != 'joomdle') {
            return;
        }

        $logoutredirecttojoomla = get_config('auth_joomdle', 'logout_redirect_to_joomla');

        // If single sign out is disabled, just redirect if needed and return.
        if (!get_config('auth_joomdle', 'single_log_out')) {
            if ($logoutredirecttojoomla) {
                $redirect = get_config('auth_joomdle', 'joomla_url') . '/index.php?option=com_joomdle&task=user.getout';
            }
            return;
        }

        $ua = core_useragent::get_user_agent_string();
        $cookiesuffix = $this->call_method("logout", [
            'username' => $USER->username,
            'ua_string' => $ua,
        ]);
        $r = 'joomla_remember_me_' . $cookiesuffix;

        // Delete user key from table in Joomla if we had a remember me cookie.
        if ((array_key_exists($r, $_COOKIE))  && ($_COOKIE[$r])) {
            $cookievalue = $_COOKIE[$r];
            $cookiearray = explode('.', $cookievalue);

            $this->call_method("deleteUserKey", ['series' => $cookiearray[1]]);
        }

        // Log out with a redirect to support cross-domain with "remember me" set.
        if (get_config('auth_joomdle', 'logout_with_redirect')) {
            $redirect = get_config('auth_joomdle', 'joomla_url') . '/index.php?option=com_joomdle&task=user.logout';
            return;
        }

        if ($r) {
            setcookie($r, false, time() - 42000, '/');
        }

        if ($logoutredirecttojoomla) {
            $redirect = get_config('auth_joomdle', 'joomla_url') .
                '/index.php?option=com_joomdle&view=wrapper&layout=getout&tmpl=component';
        }
    }

    /**
     * My badges.
     *
     * @param mixed $username Username.
     * @param mixed $n N.
     * @return mixed The result of the operation.
     */
    public function my_badges($username, $n = 10) {
        global $CFG;
        require_once($CFG->libdir . "/badgeslib.php");

        $username = strtolower($username);

        $user = get_complete_user_data('username', $username);

        if (!$user) {
            return [];
        }

        $badges = badges_get_user_badges($user->id, null, 0, $n);

        $bs = [];
        foreach ($badges as $badge) {
            $b = [];
            $b['name'] = $badge->name;
            $b['hash'] = $badge->uniquehash;

            $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
            $imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
            $b['image_url'] = (string) $imageurl;

            $bs[] = $b;
        }

        return $bs;
    }

    /**
     * My completed courses.
     *
     * @param mixed $username Username.
     * @param mixed $orderbycat Order by cat.
     * @return mixed The result of the operation.
     */
    public function my_completed_courses($username, $orderbycat = 0) {
        global $CFG, $DB;

        $courses = $this->my_courses($username, $orderbycat);

        $user = get_complete_user_data('username', $username);

        $query = "SELECT
            course as remoteid, timecompleted
            FROM
            {$CFG->prefix}course_completions cc
            LEFT JOIN {$CFG->prefix}course c ON c.id = cc.course
            WHERE
            userid = ?
            and timecompleted is not NULL";

        $params = [$user->id];
        $records = $DB->get_records_sql($query, $params);

        $completedbyid = [];
        foreach ($records as $record) {
            $completedbyid[$record->remoteid] = $record->timecompleted;
        }

        $mycompletedcourses = [];
        foreach ($courses as $course) {
            if (!array_key_exists($course['id'], $completedbyid)) {
                continue;
            }
            $course['timecompleted'] = $completedbyid[$course['id']];

            $mycompletedcourses[] = $course;
        }

        return $mycompletedcourses;
    }

    /**
     * Get completed course users.
     *
     * @param mixed $id Id.
     * @return mixed The result of the operation.
     */
    public function get_completed_course_users($id) {
        global $CFG, $DB;

        $query = "SELECT
            userid, timecompleted, username, email, firstname, lastname
            FROM
            {$CFG->prefix}course_completions cc
            LEFT JOIN {$CFG->prefix}user u ON u.id = cc.userid
            WHERE
            cc.course = ?
            and timecompleted is not NULL";

        $params = [$id];
        $records = $DB->get_records_sql($query, $params);

        $users = [];
        foreach ($records as $record) {
            $r = [];
            $r['firstname'] = $record->firstname;
            $r['lastname'] = $record->lastname;
            $r['username'] = $record->username;
            $r['email'] = $record->email;
            $r['timecompleted'] = $record->timecompleted;

            $users[] = $r;
        }

        return $users;
    }

    /**
     * Change username.
     *
     * @param mixed $oldusername Old username.
     * @param mixed $newusername New username.
     * @return mixed The result of the operation.
     */
    public function change_username($oldusername, $newusername) {
        global $CFG;

        require_once($CFG->dirroot . '/user/lib.php');

        $user = get_complete_user_data('username', $oldusername);

        if (!$user) {
            return false;
        }

        $newuser = new stdClass();
        $newuser->id = $user->id;
        $newuser->username = $newusername;

        user_update_user($newuser);

        return true;
    }

    /**
     * Logs an authenticated user into Joomla and Moodle.
     *
     * @param stdClass $user Authenticated Moodle user.
     * @param string $username Username supplied during authentication.
     * @param string $password Password supplied during authentication.
     * @return void
     */
    public function user_authenticated_hook(&$user, $username, $password) {
        global $redirect, $USER, $SESSION;

        if ($user->auth != 'joomdle') {
            return;
        }

        // We pass an empty password when login started in Joomla.
        // As Joomla user is already logged, nothing to do here.
        if ($password == '') {
            return;
        }

        // Skip this in token based logins, etc.
        if (session_id() === null) {
            return;
        }

        /* Login from password change, don't log in to Joomla */
        if (
            (array_key_exists('password', $_POST))  && (array_key_exists('newpassword1', $_POST))
            && (array_key_exists('newpassword2', $_POST))
        ) {
            return;
        }

        complete_user_login($user);

        $redirectlesssso = get_config('auth_joomdle', 'redirectless_sso');

        if ($redirectlesssso) {
            // Redirect-less login.
            $this->log_into_joomla($username, $password);
            return;
        }

        // Normal login.
        $logindata = base64_encode($username . ':' . $password);

        $wantsurl = '';
        if ((property_exists($SESSION, 'wantsurl')) && ($SESSION->wantsurl)) {
            $wantsurl = base64_encode($SESSION->wantsurl);
        }

        $this->post_login_to_joomla($logindata, $wantsurl);
    }

    /**
     * Sends the Joomla login data using a POST form instead of putting credentials in the URL.
     *
     * @param string $logindata Base64 encoded username and password.
     * @param string $wantsurl Base64 encoded return URL.
     * @return void
     */
    private function post_login_to_joomla($logindata, $wantsurl = '') {
        $posturl = get_config('auth_joomdle', 'joomla_url') .
            '/index.php?option=com_joomdle&view=joomdle&task=user.login';

        @header('Content-Type: text/html; charset=utf-8');
        @header('Referrer-Policy: no-referrer');

        echo '<!doctype html>';
        echo '<html><head><meta charset="utf-8">';
        echo '<meta name="robots" content="noindex,nofollow">';
        echo '<meta name="referrer" content="no-referrer">';
        echo '<title>Joomdle login</title>';
        echo '</head><body>';
        echo '<form id="joomdle-login-form" method="post" action="' . s($posturl) . '">';
        echo '<input type="hidden" name="data" value="' . s($logindata) . '">';
        if ($wantsurl !== '') {
            echo '<input type="hidden" name="wantsurl" value="' . s($wantsurl) . '">';
        }
        echo '<noscript><p><input type="submit" value="Continue"></p></noscript>';
        echo '</form>';
        echo '<script>document.getElementById("joomdle-login-form").submit();</script>';
        echo '</body></html>';
        exit;
    }

    /**
     * Logs the user into Joomla using cURL to set the cookies.
     *
     * @param string $username Joomla username.
     * @param string $password Joomla password.
     * @return bool True when Joomla accepted the login and its cookies were set.
     */
    public function log_into_joomla($username, $password) {
        global $CFG;

        $logindata = base64_encode($username . ':' . $password);
        $url = get_config('auth_joomdle', 'joomla_url') .
            '/index.php?option=com_joomdle&view=joomdle&task=user.login';

        $moodlehost = parse_url($CFG->wwwroot, PHP_URL_HOST);
        $joomlahost = parse_url($url, PHP_URL_HOST);
        if (!$moodlehost || !$joomlahost || strcasecmp($moodlehost, $joomlahost) !== 0) {
            debugging(get_string('redirectlessssohosterror', 'auth_joomdle'), DEBUG_NORMAL);
            return false;
        }

        $file = tempnam($CFG->tempdir, 'joomdle_');
        if ($file === false) {
            die(get_string('cantwritecurlfile', 'auth_joomdle', $file));
        }

        $curl = new curl(['cookie' => $file]);
        $curl->post(
            $url,
            http_build_query(['data' => $logindata], '', '&'),
            ['CURLOPT_FOLLOWLOCATION' => false]
        );
        $curlinfo = $curl->get_info();
        $curlerror = $curl->error;
        $httpcode = $curlinfo['http_code'] ?? 0;

        // A successful Joomla login redirects to the configured destination.
        if ($curl->get_errno() !== CURLE_OK || $httpcode < 300 || $httpcode >= 400) {
            unlink($file);
            $detail = $curlerror ?: 'HTTP ' . $httpcode;
            debugging(get_string('redirectlessssoerror', 'auth_joomdle', $detail), DEBUG_NORMAL);
            return false;
        }

        $f = fopen($file, 'ro');

        if (!$f) {
            die(get_string('cantopencurlfile', 'auth_joomdle', $file));
        }

        while (!feof($f)) {
            $line = fgets($f);
            if (($line == "\n") || (strncmp($line, '# ', 2) == 0)) {
                continue;
            }
            $parts = explode("\t", $line);
            if (array_key_exists(6, $parts)) {
                $name = $parts[5];
                $value = trim($parts[6]);
                $httponly = strncmp($parts[0], '#HttpOnly_', 10) === 0;
                setcookie($name, $value, [
                    'expires' => (int) $parts[4],
                    'path' => $parts[2] ?: '/',
                    'secure' => strtoupper($parts[3]) === 'TRUE',
                    'httponly' => $httponly,
                    'samesite' => 'Lax',
                ]);
            }
        }
        fclose($f);
        unlink($file);
        return true;
    }

    /**
     * This is a copy of the function in the Moodle core.
     * It sets the IP address of the user logging in through Joomla using redirectless SSO.
     * Without it, the Joomla server IP address is used instead.
     *
     * Call to complete the user login process after authenticate_user_login()
     * has succeeded. It will setup the $USER variable and other required bits
     * and pieces.
     *
     * NOTE:
     * - It will NOT log anything -- up to the caller to decide what to log.
     * - this function does not set any cookies any more!
     *
     * @param stdClass $user
     * @param array $extrauserinfo
     * @return stdClass A {@link $USER} object - BC only, do not use
     */
    public function complete_user_login($user, array $extrauserinfo = []) {
        global $CFG, $DB, $USER, $SESSION;

        \core\session\manager::login_user($user);

        // Reload preferences from DB.
        unset($USER->preference);
        check_user_preferences_loaded($USER);

        // Update login times.
        update_user_login_times();

        // Extra session prefs init.
        set_login_session_preferences();

        // Trigger login event.
        $event = \core\event\user_loggedin::create(
            [
                'userid' => $USER->id,
                'objectid' => $USER->id,
                'other' => [
                    'username' => $USER->username,
                    'extrauserinfo' => $extrauserinfo,
                ],
            ]
        );
        $event->trigger();

        // Allow plugins to callback as soon possible after user has completed login.
        \core\di::get(\core\hook\manager::class)->dispatch(new \core_user\hook\after_login_completed());

        // Check if the user is using a new browser or session (a new MoodleSession cookie is set in that case).
        // If the user is accessing from the same IP, ignore it (most of the time will be a new session in the same browser).
        // Skip Web Service requests, CLI scripts, AJAX scripts, and request from the mobile app itself.
        if ((array_key_exists('loginip', $extrauserinfo)) && ($extrauserinfo['loginip'])) {
            $loginip = $extrauserinfo['loginip'];

            // Function update_user_login_times() sets lastip using getremoteaddr().
            // If we have a different IP coming from a redirect-less SSO login, update the user record.
            $u = new \stdClass();
            $SESSION->lastip = $loginip;
            $USER->lastip = $u->lastip = $loginip;
            $u->id = $user->id;
            $DB->update_record('user', $u);
        } else {
            $loginip = getremoteaddr();
        }

        $isnewip = isset($SESSION->userpreviousip) && $SESSION->userpreviousip != $loginip;
        $isvalidenv = (!WS_SERVER && !CLI_SCRIPT && !NO_MOODLE_COOKIES) || PHPUNIT_TEST;

        if (!empty($SESSION->isnewsessioncookie) && $isnewip && $isvalidenv && !\core_useragent::is_moodle_app()) {
            $logintime = time();
            $ismoodleapp = false;
            $useragent = \core_useragent::get_user_agent_string();

            $sitepreferences = get_message_output_default_preferences();
            // Check if new login notification is disabled at system level.
            $newlogindisabled = $sitepreferences->moodle_newlogin_disable ?? 0;
            // Check if message providers (web, email, mobile) are enabled at system level.
            $msgproviderenabled = isset($sitepreferences->message_provider_moodle_newlogin_enabled);
            // Get message providers enabled for a user.
            $userpreferences = get_user_preferences('message_provider_moodle_newlogin_enabled');
            // Check if notification processor plugins (web, email, mobile) are enabled at system level.
            $msgprocessorsready = !empty(get_message_processors(true));
            // If new login notification is enabled at system level then go for other conditions check.
            $newloginenabled = $newlogindisabled ? 0 : ($userpreferences != 'none' && $msgproviderenabled);

            if ($newloginenabled && $msgprocessorsready) {
                // Schedule adhoc task to send a login notification to the user.
                $task = new \core\task\send_login_notifications();
                $task->set_userid($USER->id);
                $task->set_custom_data(compact('ismoodleapp', 'useragent', 'loginip', 'logintime'));
                $task->set_component('core');
                \core\task\manager::queue_adhoc_task($task);
            }
        }

        // Queue migrating the messaging data, if we need to.
        if (!get_user_preferences('core_message_migrate_data', false, $USER->id)) {
            // Check if there are any legacy messages to migrate.
            if (\core_message\helper::legacy_messages_exist($USER->id)) {
                \core_message\task\migrate_message_data::queue_task($USER->id);
            } else {
                set_user_preference('core_message_migrate_data', true, $USER->id);
            }
        }

        if (isguestuser()) {
            // No need to continue when user is THE guest.
            return $USER;
        }

        if (CLI_SCRIPT) {
            // We can redirect to password change URL only in browser.
            return $USER;
        }

        // Select password change url.
        $userauth = get_auth_plugin($USER->auth);

        // Check whether the user should be changing password.
        if (get_user_preferences('auth_forcepasswordchange', false)) {
            if ($userauth->can_change_password()) {
                if ($changeurl = $userauth->change_password_url()) {
                    redirect($changeurl);
                } else {
                    require_once($CFG->dirroot . '/login/lib.php');
                    $SESSION->wantsurl = core_login_get_return_url();
                    redirect($CFG->wwwroot . '/login/change_password.php');
                }
            } else {
                throw new \moodle_exception('nopasswordchangeforced', 'auth');
            }
        }
        return $USER;
    }
}
