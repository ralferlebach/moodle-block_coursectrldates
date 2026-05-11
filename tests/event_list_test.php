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
 * PHPUnit tests for event_list::export_for_template().
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates;

use advanced_testcase;
use block_coursectrldates\output\event_list;

/**
 * Tests for event_list.
 *
 * @covers \block_coursectrldates\output\event_list
 */
final class event_list_test extends advanced_testcase {
    /** @var int Stable timestamp used across tests (2026-06-15 12:00 UTC). */
    private const TS_A = 1781560800;

    /** @var int Stable timestamp 1 hour later than TS_A. */
    private const TS_B = 1781564400;

    /** @var int Stable timestamp the next day. */
    private const TS_NEXT_DAY = 1781647200;

    /** @var int Course ID used in tests. */
    private const CID = 99;

    /**
     * Build a minimal event array.
     *
     * @param int    $ts        Unix timestamp.
     * @param int    $cmid      CM id.
     * @param string $modname   Module name.
     * @param string $eventtype Field/event type identifier.
     * @return array
     */
    private function make_event(
        int $ts,
        int $cmid = 1,
        string $modname = 'assign',
        string $eventtype = 'duedate'
    ): array {
        return [
            'timestamp'  => $ts,
            'cmid'       => $cmid,
            'cmname'     => 'Activity ' . $cmid,
            'modname'    => $modname,
            'eventlabel' => ucfirst($eventtype),
            'eventtype'  => $eventtype,
        ];
    }

    /**
     * Helper: export a list and return the context array.
     *
     * @param array  $events     Event arrays.
     * @param int    $total      Total count (pre-truncation).
     * @param string $nomessage  Empty-state message.
     * @return array Template context.
     */
    private function export(array $events, int $total = 0, string $nomessage = ''): array {
        // PHPUnit bootstraps $OUTPUT as core\output\bootstrap_renderer, which does not
        // satisfy the renderer_base type hint. Since export_for_template() does not
        // use the renderer instance, a mock satisfies the contract correctly.
        $count  = $total > 0 ? $total : count($events);
        $output = $this->createMock(\renderer_base::class);
        $list   = new event_list($events, $count, self::CID, $nomessage);
        return $list->export_for_template($output);
    }

    // Empty list tests.

    /**
     * Empty event list sets hasdays=false.
     */
    public function test_empty_list_has_no_days(): void {
        $this->resetAfterTest();

        $ctx = $this->export([]);

        $this->assertFalse($ctx['hasdays']);
        $this->assertEmpty($ctx['days']);
    }

    /**
     * Empty list passes through the no-events message.
     */
    public function test_empty_list_carries_no_events_message(): void {
        $this->resetAfterTest();

        $ctx = $this->export([], 0, 'Nothing here.');

        $this->assertSame('Nothing here.', $ctx['noeventsmessage']);
    }

    // Day grouping tests.

    /**
     * Single event creates one day with one slot and one entry.
     */
    public function test_single_event_creates_one_day_one_slot(): void {
        $this->resetAfterTest();

        $ctx = $this->export([$this->make_event(self::TS_A, 5)]);

        $this->assertTrue($ctx['hasdays']);
        $this->assertCount(1, $ctx['days']);
        $this->assertCount(1, $ctx['days'][0]['slots']);
        $this->assertCount(1, $ctx['days'][0]['slots'][0]['entries']);
    }

    /**
     * Two events at the same timestamp share one slot.
     */
    public function test_same_timestamp_shares_slot(): void {
        $this->resetAfterTest();

        $events = [
            $this->make_event(self::TS_A, 1),
            $this->make_event(self::TS_A, 2),
        ];
        $ctx = $this->export($events);

        $this->assertCount(1, $ctx['days']);
        $this->assertCount(1, $ctx['days'][0]['slots']);
        $this->assertCount(2, $ctx['days'][0]['slots'][0]['entries']);
    }

    /**
     * Two events at different timestamps on the same day get two slots.
     */
    public function test_different_timestamps_same_day_get_two_slots(): void {
        $this->resetAfterTest();

        $events = [
            $this->make_event(self::TS_A, 1),
            $this->make_event(self::TS_B, 2),
        ];
        $ctx = $this->export($events);

        $this->assertCount(1, $ctx['days']);
        $this->assertCount(2, $ctx['days'][0]['slots']);
    }

    /**
     * Events on different days produce separate day cards.
     */
    public function test_events_on_different_days_get_separate_day_cards(): void {
        $this->resetAfterTest();

        $events = [
            $this->make_event(self::TS_A, 1),
            $this->make_event(self::TS_NEXT_DAY, 2),
        ];
        $ctx = $this->export($events);

        $this->assertCount(2, $ctx['days']);
    }

    // URL tests.

    /**
     * Slot-shift URL contains courseid and autoopen=slot parameters.
     */
    public function test_slot_shift_url_contains_correct_params(): void {
        $this->resetAfterTest();

        $ctx = $this->export([$this->make_event(self::TS_A)]);

        $sloturl = $ctx['days'][0]['slots'][0]['shiftsloturl'];
        $this->assertStringContainsString('courseid=' . self::CID, $sloturl);
        $this->assertStringContainsString('autoopen=slot', $sloturl);
        $this->assertStringContainsString('shift_ts=' . self::TS_A, $sloturl);
    }

    /**
     * Following-shift URL contains autoopen=following.
     */
    public function test_following_shift_url_contains_correct_params(): void {
        $this->resetAfterTest();

        $ctx = $this->export([$this->make_event(self::TS_A)]);

        $url = $ctx['days'][0]['slots'][0]['shiftfollowingurl'];
        $this->assertStringContainsString('autoopen=following', $url);
    }

    /**
     * Entry-shift URL contains autoopen=entry, cmid, and shift_field.
     */
    public function test_entry_shift_url_contains_correct_params(): void {
        $this->resetAfterTest();

        $ctx = $this->export([$this->make_event(self::TS_A, 7, 'assign', 'duedate')]);

        $url = $ctx['days'][0]['slots'][0]['entries'][0]['shiftentryurl'];
        $this->assertStringContainsString('autoopen=entry', $url);
        $this->assertStringContainsString('shift_cmid=7', $url);
        $this->assertStringContainsString('shift_field=duedate', $url);
    }

    // Truncation tests.

    /**
     * When shown < total, istruncated is true.
     */
    public function test_truncation_flag_set_when_shown_less_than_total(): void {
        $this->resetAfterTest();

        $events = [$this->make_event(self::TS_A)];
        $ctx    = $this->export($events, 5); // 1 shown, 5 total

        $this->assertTrue($ctx['istruncated']);
        $this->assertSame(1, $ctx['shown']);
        $this->assertSame(5, $ctx['total']);
    }

    /**
     * When shown equals total, istruncated is false.
     */
    public function test_no_truncation_when_shown_equals_total(): void {
        $this->resetAfterTest();

        $events = [$this->make_event(self::TS_A)];
        $ctx    = $this->export($events, 1);

        $this->assertFalse($ctx['istruncated']);
    }

    // Label / URL key presence tests.

    /**
     * All required template keys are present in the export.
     */
    public function test_all_required_keys_are_present(): void {
        $this->resetAfterTest();

        $ctx = $this->export([$this->make_event(self::TS_A)]);

        $required = [
            'hasdays', 'days', 'istruncated', 'shown', 'total',
            'timelineurl', 'opentimelinelabel', 'viewalllabel',
            'noeventsmessage', 'shiftslotlabel', 'shiftfollowinglabel', 'shiftentrylabel',
        ];
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, $ctx, "Missing key: $key");
        }
    }

    /**
     * Timeline URL contains the course ID.
     */
    public function test_timeline_url_contains_courseid(): void {
        $this->resetAfterTest();

        $ctx = $this->export([]);

        $this->assertStringContainsString('courseid=' . self::CID, $ctx['timelineurl']);
    }
}
