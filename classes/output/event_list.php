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

namespace block_coursectrldates\output;

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Renderable for the chronological list of upcoming course events.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event list renderable.
 */
class event_list implements renderable, templatable {

    /** @var array List of normalised event arrays from event_provider. */
    private array $events;

    /** @var int Total number of events available (may exceed displayed set). */
    private int $totalcount;

    /** @var int Course ID used for building shift/timeline URLs. */
    private int $courseid;

    /**
     * Constructor.
     *
     * @param array $events     Normalised event arrays to display.
     * @param int   $totalcount Total available events (for truncation notice).
     * @param int   $courseid   Course ID for building action URLs.
     */
    public function __construct(array $events, int $totalcount, int $courseid) {
        $this->events = $events;
        $this->totalcount = $totalcount;
        $this->courseid = $courseid;
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output Renderer instance.
     * @return array Template context.
     */
    public function export_for_template(renderer_base $output): array {
        $shown = count($this->events);
        $istruncated = $shown < $this->totalcount;

        $timelineurl = new moodle_url('/local/coursectrl/timeline.php');
        $timelineurl->param('courseid', $this->courseid);

        $items = [];
        foreach ($this->events as $event) {
            $shifturl = new moodle_url('/local/coursectrl/manage.php');
            $shifturl->param('courseid', $this->courseid);
            $shifturl->param('cmid', $event['cmid'] ?? 0);
            $shifturl->param('action', 'shift_dates');

            $items[] = [
                'timestamp'    => $event['timestamp'] ?? 0,
                'dateformatted' => userdate($event['timestamp'] ?? 0, get_string('strftimedaydatetime', 'core_langconfig')),
                'cmname'       => $event['cmname'] ?? '',
                'eventlabel'   => $event['eventlabel'] ?? '',
                'shifturl'     => $shifturl->out(false),
            ];
        }

        return [
            'hasevents'      => !empty($items),
            'events'         => $items,
            'istruncated'    => $istruncated,
            'shown'          => $shown,
            'total'          => $this->totalcount,
            'timelineurl'    => $timelineurl->out(false),
        ];
    }
}
