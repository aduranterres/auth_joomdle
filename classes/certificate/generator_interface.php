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
 * Contract implemented by certificate module adapters.
 *
 * @package    auth_joomdle
 * @copyright  2009 Antonio Duran Terres
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface generator_interface {
    /**
     * Generates a certificate for a Moodle user.
     *
     * @param \stdClass $user Target Moodle user.
     * @param int $cmid Course module id.
     * @return array{filename: string, mimetype: string, content: string}
     */
    public function generate(\stdClass $user, int $cmid): array;
}
