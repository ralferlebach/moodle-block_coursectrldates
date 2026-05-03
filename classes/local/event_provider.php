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

namespace block_coursectrldates\local;

/**
 * Retrieves upcoming course-date events from local_coursectrl.
 *
 * This class acts as the integration point between the block and the
 * inventory/timeline services provided by local_coursectrl. It returns
 * a normalised list of upcoming events sorted by date.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event provider stub for block_coursectrldates.
 *
 * Real implementation will delegate to local_coursectrl services once
 * the required service interface has been agreed upon (see open technical
 * points in the blueprint, section 6).
 */
class event_provider {

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
     * Return upcoming events within a time window.
     *
     * Each returned element is an associative array with keys:
     *   - timestamp (int)   Unix timestamp of the event.
     *   - cmid      (int)   Course-module ID.
     *   - cmname    (string) Display name of the activity.
     *   - eventtype (string) Machine-readable event type (e.g. 'open', 'close').
     *   - eventlabel (string) Localised event description.
     *
     * @param int $weeks Number of weeks ahead to include.
     * @return array Sorted list of event arrays.
     */
    public function get_events_by_window(int $weeks): array {
        // TODO: delegate to local_coursectrl inventory/timeline service.
        return [];
    }

    /**
     * Return the next N upcoming events.
     *
     * @param int $count Maximum number of events to return.
     * @return array Sorted list of event arrays (same structure as get_events_by_window).
     */
    public function get_events_by_count(int $count): array {
        // TODO: delegate to local_coursectrl inventory/timeline service.
        return [];
    }
}
