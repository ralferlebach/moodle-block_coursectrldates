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
 * The README states the release of version.php (release gate).
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates;

#[\PHPUnit\Framework\Attributes\CoversNothing]
/**
 * README and version.php must not disagree.
 *
 * @coversNothing
 */
final class readme_test extends \basic_testcase {
    /**
     * The release line and the declared dependencies match version.php.
     */
    public function test_readme_matches_version_php(): void {
        $plugin = new \stdClass();
        include(__DIR__ . '/../version.php');
        $readme = file_get_contents(__DIR__ . '/../README.md');
        $tick = chr(96);
        $this->assertStringContainsString(
            '**Release:** ' . $tick . $plugin->release . $tick . ' (' . $tick . $plugin->version . $tick . ')',
            $readme,
            'README.md must state the release of version.php'
        );
        foreach ($plugin->dependencies ?? [] as $component => $version) {
            $this->assertStringContainsString((string) $version, $readme, "README.md must name the $component version");
        }
    }
}
