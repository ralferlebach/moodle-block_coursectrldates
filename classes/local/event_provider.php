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
 * Event provider for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\local;

use local_coursectrl\local\analysis\date_collector;
use local_coursectrl\local\inventory\inventory_service;

/**
 * Retrieves upcoming course-date events via local_coursectrl services.
 *
 * Delegates entirely to local_coursectrl's inventory_service (cm_item
 * collection) and date_collector (adapter + cm-level date aggregation).
 * No subplugin classes are referenced directly from this block.
 *
 * local_coursectrl is a hard dependency declared in version.php; Moodle's
 * plugin-manager enforces its presence before installation, so no
 * defensive class_exists guard is needed here.
 */
class event_provider {
    /** @var int Seconds to look ahead when count mode needs a far horizon. */
    private const HORIZON_SECS = 52 * WEEKSECS;

    /** @var int Course ID this provider is scoped to. */
    private int $courseid;

    /**
     * Constructor.
     *
     * @param int $courseid Moodle course ID.
     */
    public function __construct(int $courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Return all upcoming events within a time window.
     *
     * Returns an associative array with:
     *   - 'events' (array)  Sorted event arrays (see below).
     *   - 'total'  (int)    Total count (equals count of events).
     *
     * Each event array contains:
     *   - timestamp  (int)    Unix timestamp.
     *   - cmid       (int)    Course-module ID.
     *   - cmname     (string) Activity display name.
     *   - eventtype  (string) Field identifier, e.g. 'duedate'.
     *   - eventlabel (string) Localised field label.
     *
     * @param int $weeks Number of weeks ahead to include.
     * @return array{events: array, total: int}
     */
    public function get_events_by_window(int $weeks): array {
        $now = time();
        $timeto = $now + ($weeks * WEEKSECS);
        $all = $this->build_events($now, $timeto);
        return [
            'events' => $all,
            'total'  => count($all),
        ];
    }

    /**
     * Return the next N upcoming events.
     *
     * Returns an associative array with:
     *   - 'events' (array)  Up to $count sorted event arrays.
     *   - 'total'  (int)    Total events found before truncation.
     *
     * @param int $count Maximum number of events to return.
     * @return array{events: array, total: int}
     */
    public function get_events_by_count(int $count): array {
        $now = time();
        $timeto = $now + self::HORIZON_SECS;
        $all = $this->build_events($now, $timeto);
        return [
            'events' => array_slice($all, 0, $count),
            'total'  => count($all),
        ];
    }

    /**
     * Collect all events in a time window via local_coursectrl services.
     *
     * Uses inventory_service to obtain the cm_item list, then delegates
     * date aggregation to date_collector, which handles adapter calls and
     * cm-level fields (completionexpected, availability dates) in one pass.
     *
     * date_collector returns entries sorted chronologically; the filter loop
     * preserves that order.
     *
     * @param int $timefrom Start of the window (inclusive).
     * @param int $timeto   End of the window (exclusive).
     * @return array Sorted event arrays.
     */
    private function build_events(int $timefrom, int $timeto): array {
        $snapshot = (new inventory_service())->build_for_course($this->courseid);
        $allentries = (new date_collector())->collect($snapshot->cms);

        $events = [];
        foreach ($allentries as $entry) {
            $ts = (int) $entry['timestamp'];
            if ($ts < $timefrom || $ts >= $timeto) {
                continue;
            }
            $events[] = [
                'timestamp'  => $ts,
                'cmid'       => (int) $entry['cmid'],
                'cmname'     => (string) $entry['name'],
                'eventtype'  => (string) $entry['field'],
                'eventlabel' => (string) $entry['fieldlabel'],
            ];
        }

        return $events;
    }
}
