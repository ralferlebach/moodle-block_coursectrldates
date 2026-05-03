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
 * Unit tests for block_coursectrldates\local\config_reader.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates;

use advanced_testcase;
use block_coursectrldates\local\config_reader;

/**
 * Tests for config_reader.
 *
 * Pure unit tests — no DB access, no resetAfterTest() needed.
 *
 * @covers \block_coursectrldates\local\config_reader
 */
final class config_reader_test extends advanced_testcase {
    /**
     * Null config produces correct defaults for all accessors.
     *
     * @return void
     */
    public function test_null_config_returns_defaults(): void {
        $reader = new config_reader(null);

        $this->assertFalse($reader->show_calendar());
        $this->assertSame(config_reader::DEFAULT_CALENDAR_WEEKS, $reader->calendar_weeks());
        $this->assertSame(config_reader::MODE_TIMEWINDOW, $reader->list_mode());
        $this->assertSame(config_reader::DEFAULT_LIST_WEEKS, $reader->list_weeks());
        $this->assertSame(config_reader::DEFAULT_LIST_COUNT, $reader->list_count());
        $this->assertFalse($reader->show_help());
        $this->assertSame(config_reader::DEFAULT_HELP_WEEKS, $reader->help_window_weeks());
        $this->assertFalse($reader->help_trigger_new());
        $this->assertFalse($reader->help_trigger_reset());
        $this->assertFalse($reader->help_trigger_timedeps());
        $this->assertFalse($reader->reset_help());
    }

    /**
     * Empty stdClass config also produces defaults.
     *
     * @return void
     */
    public function test_empty_config_returns_defaults(): void {
        $reader = new config_reader(new \stdClass());

        $this->assertFalse($reader->show_calendar());
        $this->assertSame(config_reader::MODE_TIMEWINDOW, $reader->list_mode());
        $this->assertFalse($reader->show_help());
    }

    /**
     * show_calendar() reflects truthy config values.
     *
     * @return void
     */
    public function test_show_calendar_truthy(): void {
        $this->assertTrue((new config_reader((object)['show_calendar' => 1]))->show_calendar());
        $this->assertTrue((new config_reader((object)['show_calendar' => '1']))->show_calendar());
        $this->assertFalse((new config_reader((object)['show_calendar' => 0]))->show_calendar());
    }

    /**
     * calendar_weeks() clamps values to the 1–6 range.
     *
     * @return void
     */
    public function test_calendar_weeks_clamped(): void {
        $cases = [
            [0, 1],
            [-5, 1],
            [1, 1],
            [4, 4],
            [6, 6],
            [7, 6],
            [99, 6],
        ];
        foreach ($cases as [$input, $expected]) {
            $config = (object)['calendar_weeks' => $input];
            $this->assertSame(
                $expected,
                (new config_reader($config))->calendar_weeks(),
                "calendar_weeks($input) should be $expected"
            );
        }
    }

    /**
     * list_mode() accepts known modes and falls back for unknown values.
     *
     * @return void
     */
    public function test_list_mode_validation(): void {
        $this->assertSame(
            config_reader::MODE_COUNT,
            (new config_reader((object)['list_mode' => config_reader::MODE_COUNT]))->list_mode()
        );
        $this->assertSame(
            config_reader::MODE_TIMEWINDOW,
            (new config_reader((object)['list_mode' => 'invalid']))->list_mode(),
            'Unknown list_mode should fall back to MODE_TIMEWINDOW'
        );
    }

    /**
     * list_weeks() clamps values to the 1–6 range.
     *
     * @return void
     */
    public function test_list_weeks_clamped(): void {
        $this->assertSame(1, (new config_reader((object)['list_weeks' => 0]))->list_weeks());
        $this->assertSame(6, (new config_reader((object)['list_weeks' => 10]))->list_weeks());
        $this->assertSame(3, (new config_reader((object)['list_weeks' => 3]))->list_weeks());
    }

    /**
     * list_count() enforces a minimum of 1.
     *
     * @return void
     */
    public function test_list_count_minimum(): void {
        $this->assertSame(1, (new config_reader((object)['list_count' => 0]))->list_count());
        $this->assertSame(1, (new config_reader((object)['list_count' => -5]))->list_count());
        $this->assertSame(25, (new config_reader((object)['list_count' => 25]))->list_count());
    }

    /**
     * help_window_weeks() clamps values to the 1–6 range.
     *
     * @return void
     */
    public function test_help_window_weeks_clamped(): void {
        $this->assertSame(1, (new config_reader((object)['help_window_weeks' => 0]))->help_window_weeks());
        $this->assertSame(6, (new config_reader((object)['help_window_weeks' => 10]))->help_window_weeks());
        $this->assertSame(2, (new config_reader((object)['help_window_weeks' => 2]))->help_window_weeks());
    }

    /**
     * Help trigger flags reflect boolean config.
     *
     * @return void
     */
    public function test_help_trigger_flags(): void {
        $config = (object)[
            'show_help'            => 1,
            'help_trigger_new'     => 1,
            'help_trigger_reset'   => 1,
            'help_trigger_timedeps' => 1,
            'reset_help'           => 1,
        ];
        $reader = new config_reader($config);
        $this->assertTrue($reader->show_help());
        $this->assertTrue($reader->help_trigger_new());
        $this->assertTrue($reader->help_trigger_reset());
        $this->assertTrue($reader->help_trigger_timedeps());
        $this->assertTrue($reader->reset_help());

        $reader2 = new config_reader(new \stdClass());
        $this->assertFalse($reader2->help_trigger_new());
        $this->assertFalse($reader2->help_trigger_reset());
        $this->assertFalse($reader2->help_trigger_timedeps());
    }
}
