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

namespace auth_joomdle\certificate;

/**
 * Selects the certificate adapter and prepares its service response.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manager {
    /**
     * Generates a certificate encoded for transport through a web service.
     *
     * @param string $username Target Moodle username.
     * @param string $type Certificate module type.
     * @param int $cmid Course module id.
     * @return array{filename: string, mimetype: string, encoding: string, content: string}
     */
    public function generate(string $username, string $type, int $cmid): array {
        $user = \core_user::get_user_by_username($username, '*', null, MUST_EXIST);
        if ($user->deleted || $user->suspended || !$user->confirmed || isguestuser($user)) {
            throw new \invalid_parameter_exception('The target user cannot receive a certificate');
        }

        $generator = $this->get_generator($type);
        $certificate = $generator->generate($user, $cmid);

        return [
            'filename' => $certificate['filename'],
            'mimetype' => $certificate['mimetype'],
            'encoding' => 'base64',
            'content' => base64_encode($certificate['content']),
        ];
    }

    /**
     * Returns the adapter for a certificate type.
     *
     * Add future certificate modules here without changing the external service.
     *
     * @param string $type Certificate module type.
     * @return generator_interface
     */
    private function get_generator(string $type): generator_interface {
        switch ($type) {
            case 'custom':
                return new customcert_generator();
            case 'coursecertificate':
                return new coursecertificate_generator();
            case 'simple':
                return new simplecertificate_generator();
            default:
                throw new \invalid_parameter_exception('Unsupported certificate type: ' . $type);
        }
    }
}
