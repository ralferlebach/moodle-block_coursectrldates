<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Coverage scope for block_coursectrldates.
 *
 * Limited to the code that carries the behaviour worth measuring: the classes and the plugin's
 * library/callback files. Entry-point scripts are exercised by Behat rather than PHPUnit.
 *
 * @package    block_coursectrldates
 * @category   test
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Coverage scope definition (the global class name is the 4.5 class and a maintained alias on 5.x).
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
return new class extends \phpunit_coverage_info {
    /** @var array Directories whose files are included in coverage. */
    protected $includelistfolders = [
        'classes',
    ];

    /** @var array Individual files included in coverage. */
    protected $includelistfiles = [
        'block_coursectrldates.php',
    ];
};
