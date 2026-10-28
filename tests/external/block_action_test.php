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
 * PHPUnit tests for the block_action external service.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\external;

use advanced_testcase;
use block_coursectrldates\local\splash_state;

#[\PHPUnit\Framework\Attributes\CoversClass(\block_coursectrldates\external\block_action::class)]
/**
 * Tests for block_action.
 *
 * @covers \block_coursectrldates\external\block_action
 */
final class block_action_test extends advanced_testcase {
    /**
     * Create a course with an editing teacher and a coursectrldates block instance.
     *
     * @param \stdClass|null $config Optional block configuration to store.
     * @return array{course: \stdClass, user: \stdClass, instanceid: int}
     */
    private function create_fixture(?\stdClass $config = null): array {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $record = [
            'blockname' => 'coursectrldates',
            'parentcontextid' => \context_course::instance($course->id)->id,
            'showinsubcontexts' => 0,
            'pagetypepattern' => 'course-view-*',
            'defaultregion' => 'side-pre',
            'defaultweight' => 0,
            'configdata' => $config ? base64_encode(serialize($config)) : '',
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $instanceid = (int) $DB->insert_record('block_instances', (object) $record);
        \context_block::instance($instanceid);
        return ['course' => $course, 'user' => $user, 'instanceid' => $instanceid];
    }

    /**
     * Read and decode the configdata of a block instance.
     *
     * @param int $instanceid Block instance id.
     * @return \stdClass
     */
    private function read_config(int $instanceid): \stdClass {
        global $DB;
        $raw = $DB->get_field('block_instances', 'configdata', ['id' => $instanceid]);
        // phpcs:disable moodle.PHP.ForbiddenFunctions.Found -- Test reads back Moodle-written configdata.
        $config = unserialize(base64_decode($raw), ['allowed_classes' => [\stdClass::class]]);
        // phpcs:enable moodle.PHP.ForbiddenFunctions.Found
        $this->assertInstanceOf(\stdClass::class, $config);
        return $config;
    }

    /**
     * Disabling the help keeps existing settings and stores show_help = 0.
     */
    public function test_disable_help_updates_existing_config(): void {
        $this->resetAfterTest();
        $config = (object) ['show_help' => '1', 'list_weeks' => 6];
        $fixture = $this->create_fixture($config);
        $this->setUser($fixture['user']);

        $result = block_action::execute($fixture['instanceid'], (int) $fixture['course']->id, 'disable_help');

        $this->assertTrue($result);
        $stored = $this->read_config($fixture['instanceid']);
        $this->assertSame('0', $stored->show_help);
        $this->assertSame(6, $stored->list_weeks);
        $state = new splash_state($fixture['instanceid'], (int) $fixture['user']->id);
        $this->assertTrue($state->is_dismissed());
    }

    /**
     * Disabling the help works when the instance has no configuration yet.
     */
    public function test_disable_help_without_existing_config(): void {
        $this->resetAfterTest();
        $fixture = $this->create_fixture();
        $this->setUser($fixture['user']);

        block_action::execute($fixture['instanceid'], (int) $fixture['course']->id, 'disable_help');

        $this->assertSame('0', $this->read_config($fixture['instanceid'])->show_help);
    }

    /**
     * Dismissing the help only stores a user preference.
     */
    public function test_dismiss_help_sets_preference_only(): void {
        global $DB;
        $this->resetAfterTest();
        $fixture = $this->create_fixture();
        $this->setUser($fixture['user']);

        block_action::execute($fixture['instanceid'], (int) $fixture['course']->id, 'dismiss_help');

        $state = new splash_state($fixture['instanceid'], (int) $fixture['user']->id);
        $this->assertTrue($state->is_dismissed());
        $this->assertSame('', $DB->get_field('block_instances', 'configdata', ['id' => $fixture['instanceid']]));
    }

    /**
     * A course id that does not own the block instance is rejected.
     */
    public function test_rejects_foreign_course(): void {
        $this->resetAfterTest();
        $fixture = $this->create_fixture();
        $other = $this->getDataGenerator()->create_course();
        $this->setUser($fixture['user']);

        $this->expectException(\moodle_exception::class);
        block_action::execute($fixture['instanceid'], (int) $other->id, 'dismiss_help');
    }

    /**
     * Unknown actions are rejected.
     */
    public function test_rejects_unknown_action(): void {
        $this->resetAfterTest();
        $fixture = $this->create_fixture();
        $this->setUser($fixture['user']);

        $this->expectException(\moodle_exception::class);
        block_action::execute($fixture['instanceid'], (int) $fixture['course']->id, 'explode');
    }
}
