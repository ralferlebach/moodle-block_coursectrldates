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
 * PHPUnit tests for setup_help_detector.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates;

use advanced_testcase;
use block_coursectrldates\local\config_reader;
use block_coursectrldates\local\setup_help_detector;

/**
 * Tests for setup_help_detector.
 *
 * Covers all three trigger conditions (trigger_new, trigger_reset,
 * trigger_timedeps) with positive, negative, and disabled variants.
 *
 * @covers \block_coursectrldates\local\setup_help_detector
 */
final class setup_help_detector_test extends advanced_testcase {
    /**
     * Build a config_reader with only the help-trigger settings populated.
     *
     * @param bool $triggernew      Activate the course-newly-created trigger.
     * @param bool $triggerreset    Activate the course-reset trigger.
     * @param bool $triggertimedeps Activate the time-dependencies trigger.
     * @param int  $windowweeks     Trigger time window in weeks (1-6).
     * @return config_reader
     */
    private function make_config(
        bool $triggernew = false,
        bool $triggerreset = false,
        bool $triggertimedeps = false,
        int $windowweeks = 4
    ): config_reader {
        $cfg = new \stdClass();
        $cfg->help_trigger_new = $triggernew ? 1 : 0;
        $cfg->help_trigger_reset = $triggerreset ? 1 : 0;
        $cfg->help_trigger_timedeps = $triggertimedeps ? 1 : 0;
        $cfg->help_window_weeks = $windowweeks;
        return new config_reader($cfg);
    }

    /**
     * Insert a minimal course-reset log entry for testing trigger_reset.
     *
     * Skips the current test if logstore_standard_log does not exist.
     *
     * @param int $courseid    Course the reset was performed on.
     * @param int $timecreated Unix timestamp for the log record.
     * @return void
     */
    private function insert_reset_log(int $courseid, int $timecreated): void {
        global $DB;

        if (!$DB->get_manager()->table_exists('logstore_standard_log')) {
            $this->markTestSkipped('logstore_standard_log not available in this environment.');
        }

        $context = \context_course::instance($courseid);
        $DB->insert_record('logstore_standard_log', (object) [
            'eventname'         => '\core\event\course_reset_started',
            'component'         => 'core',
            'action'            => 'reset',
            'target'            => 'course',
            'objecttable'       => null,
            'objectid'          => null,
            'crud'              => 'u',
            'edulevel'          => 2,
            'contextid'         => $context->id,
            'contextlevel'      => CONTEXT_COURSE,
            'contextinstanceid' => $courseid,
            'userid'            => 2,
            'courseid'          => $courseid,
            'relateduserid'     => null,
            'anonymous'         => 0,
            'other'             => null,
            'timecreated'       => $timecreated,
            'origin'            => 'web',
            'ip'                => '127.0.0.1',
            'realuserid'        => null,
        ]);
    }

    // Trigger_new tests.

    /**
     * Trigger_new fires when the course was created within the time window.
     */
    public function test_trigger_new_fires_within_window(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $DB->set_field('course', 'timecreated', time() - WEEKSECS, ['id' => $course->id]);

        $detector = new setup_help_detector();
        $config = $this->make_config(true);

        $this->assertTrue($detector->should_show($course->id, $config, []));
    }

    /**
     * Trigger_new does not fire when the course was created before the window.
     */
    public function test_trigger_new_outside_window_returns_false(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $DB->set_field('course', 'timecreated', time() - (5 * WEEKSECS), ['id' => $course->id]);

        $detector = new setup_help_detector();
        $config = $this->make_config(true);

        $this->assertFalse($detector->should_show($course->id, $config, []));
    }

    /**
     * Trigger_new does not fire when the trigger is disabled, even if in window.
     */
    public function test_trigger_new_disabled_returns_false(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $DB->set_field('course', 'timecreated', time() - WEEKSECS, ['id' => $course->id]);

        $detector = new setup_help_detector();
        $config = $this->make_config(false);

        $this->assertFalse($detector->should_show($course->id, $config, []));
    }

    // Trigger_reset tests.

    /**
     * Trigger_reset fires when a reset log entry exists within the window.
     */
    public function test_trigger_reset_fires_within_window(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->insert_reset_log($course->id, time() - WEEKSECS);

        $detector = new setup_help_detector();
        $config = $this->make_config(false, true);

        $this->assertTrue($detector->should_show($course->id, $config, []));
    }

    /**
     * Trigger_reset does not fire when the reset happened before the window.
     */
    public function test_trigger_reset_outside_window_returns_false(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->insert_reset_log($course->id, time() - (5 * WEEKSECS));

        $detector = new setup_help_detector();
        $config = $this->make_config(false, true);

        $this->assertFalse($detector->should_show($course->id, $config, []));
    }

    /**
     * Trigger_reset does not fire when the trigger is disabled.
     */
    public function test_trigger_reset_disabled_returns_false(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $this->insert_reset_log($course->id, time() - WEEKSECS);

        $detector = new setup_help_detector();
        $config = $this->make_config(false, false);

        $this->assertFalse($detector->should_show($course->id, $config, []));
    }

    // Trigger_timedeps tests.

    /**
     * Trigger_timedeps fires when a course module was added within the window.
     */
    public function test_trigger_timedeps_fires_within_window(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $module = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $DB->set_field('course_modules', 'added', time() - WEEKSECS, ['id' => $module->cmid]);

        $cms = [$module->cmid => (object) []];

        $detector = new setup_help_detector();
        $config = $this->make_config(false, false, true);

        $this->assertTrue($detector->should_show($course->id, $config, $cms));
    }

    /**
     * Trigger_timedeps does not fire when all modules were added before the window.
     */
    public function test_trigger_timedeps_outside_window_returns_false(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $module = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $DB->set_field('course_modules', 'added', time() - (5 * WEEKSECS), ['id' => $module->cmid]);

        $cms = [$module->cmid => (object) []];

        $detector = new setup_help_detector();
        $config = $this->make_config(false, false, true);

        $this->assertFalse($detector->should_show($course->id, $config, $cms));
    }

    /**
     * Trigger_timedeps does not fire when the trigger is disabled.
     */
    public function test_trigger_timedeps_disabled_returns_false(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $module = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $DB->set_field('course_modules', 'added', time() - WEEKSECS, ['id' => $module->cmid]);

        $cms = [$module->cmid => (object) []];

        $detector = new setup_help_detector();
        $config = $this->make_config(false, false, false);

        $this->assertFalse($detector->should_show($course->id, $config, $cms));
    }

    /**
     * Trigger_timedeps does not fire when the cms array is empty.
     */
    public function test_trigger_timedeps_empty_cms_returns_false(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        $detector = new setup_help_detector();
        $config = $this->make_config(false, false, true);

        $this->assertFalse($detector->should_show($course->id, $config, []));
    }

    // Combined tests.

    /**
     * Returns false immediately when all triggers are disabled.
     */
    public function test_all_triggers_disabled_returns_false(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        $detector = new setup_help_detector();
        $config = $this->make_config();

        $this->assertFalse($detector->should_show($course->id, $config, []));
    }

    /**
     * Returns false when all triggers are enabled but nothing matches the window.
     */
    public function test_no_trigger_matches_returns_false(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $DB->set_field('course', 'timecreated', time() - (5 * WEEKSECS), ['id' => $course->id]);

        $module = $this->getDataGenerator()->create_module('assign', ['course' => $course->id]);
        $DB->set_field('course_modules', 'added', time() - (5 * WEEKSECS), ['id' => $module->cmid]);

        $cms = [$module->cmid => (object) []];

        $detector = new setup_help_detector();
        $config = $this->make_config(true, true, true);

        $this->assertFalse($detector->should_show($course->id, $config, $cms));
    }

    /**
     * Returns true as soon as the first matching trigger fires (short-circuit).
     *
     * Only trigger_new matches; trigger_reset and trigger_timedeps are enabled
     * but have no matching data.
     */
    public function test_first_matching_trigger_short_circuits(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $DB->set_field('course', 'timecreated', time() - WEEKSECS, ['id' => $course->id]);

        $detector = new setup_help_detector();
        $config = $this->make_config(true, true, true);

        $this->assertTrue($detector->should_show($course->id, $config, []));
    }
}
