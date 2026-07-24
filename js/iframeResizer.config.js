// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Configure iframe-resizer when Moodle is embedded by Joomdle.
 *
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
(function() {
    'use strict';

    var joomlaOrigin = '';
    var parentOrigin = '';

    if (window.self === window.top) {
        return;
    }

    try {
        if (window.location.ancestorOrigins && window.location.ancestorOrigins.length) {
            parentOrigin = window.location.ancestorOrigins[0];
        } else if (document.referrer) {
            parentOrigin = new URL(document.referrer).origin;
        }
    } catch (error) {
        return;
    }

    if (parentOrigin !== joomlaOrigin) {
        return;
    }

    window.iframeResizer = Object.assign({}, window.iframeResizer, {
        license: 'GPLv3',
        targetOrigin: joomlaOrigin
    });
}());
