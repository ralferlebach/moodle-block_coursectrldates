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
 * Renderable for the chronological list of upcoming course events.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\output;

use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Event list renderable.
 *
 * Groups a flat sorted list of upcoming events into the day → slot → entry
 * hierarchy that event_list.mustache expects, matching the structure used by
 * local_coursectrl/timeline. Each slot and entry carries the correct
 * autoopen URLs so that the shift dialog opens immediately on timeline.php.
 */
class event_list implements renderable, templatable {
    /** @var array Sorted upcoming event arrays. */
    private array $events;

    /** @var int Total events available before any truncation. */
    private int $totalcount;

    /** @var int Course ID for building URLs. */
    private int $courseid;

    /** @var string Message shown when there are no events. */
    private string $noeventsmessage;

    /**
     * Constructor.
     *
     * @param array  $events          Sorted event arrays.
     * @param int    $totalcount      Total available events (pre-truncation).
     * @param int    $courseid        Course ID for URL building.
     * @param string $noeventsmessage Localised empty-state message.
     */
    public function __construct(
        array $events,
        int $totalcount,
        int $courseid,
        string $noeventsmessage = ''
    ) {
        $this->events = $events;
        $this->totalcount = $totalcount;
        $this->courseid = $courseid;
        $this->noeventsmessage = $noeventsmessage;
    }

    /**
     * Build a timeline.php URL that pre-opens the shift dialog.
     *
     * @param string $autoopen 'slot' | 'following' | 'entry'
     * @param int    $ts       Unix timestamp (slot / following).
     * @param int    $cmid     CM id (entry).
     * @param string $field    Field name (entry).
     * @return string
     */
    private function shift_url(
        string $autoopen,
        int $ts = 0,
        int $cmid = 0,
        string $field = ''
    ): string {
        $url = new moodle_url('/local/coursectrl/timeline.php');
        $url->param('courseid', $this->courseid);
        $url->param('autoopen', $autoopen);
        if ($ts > 0) {
            $url->param('shift_ts', $ts);
        }
        if ($cmid > 0) {
            $url->param('shift_cmid', $cmid);
        }
        if ($field !== '') {
            $url->param('shift_field', $field);
        }
        return $url->out(false);
    }

    /**
     * Export data for event_list.mustache.
     *
     * Groups events into the day → slot → entry structure the template
     * requires. All link targets carry the autoopen parameters so that
     * the shift dialog opens immediately on timeline.php without extra clicks.
     *
     * @param renderer_base $output Renderer instance.
     * @return array Template context.
     */
    public function export_for_template(renderer_base $output): array {
        $now = time();
        $dayformat  = get_string('strftimedaydate', 'core_langconfig');
        $timeformat = get_string('strftimetime24', 'core_langconfig');

        $timelineurl = new moodle_url('/local/coursectrl/timeline.php');
        $timelineurl->param('courseid', $this->courseid);
        $timelineurlstr = $timelineurl->out(false);

        // Group events into days, then slots within each day.
        $daygroups = [];
        foreach ($this->events as $event) {
            $ts      = (int) $event['timestamp'];
            $daykey  = date('Y-m-d', $ts);
            $slotkey = $ts;

            if (!isset($daygroups[$daykey])) {
                $daygroups[$daykey] = [
                    'daykey'       => $daykey,
                    'dayformatted' => userdate($ts, $dayformat),
                    'ispast'       => false,
                    'slots'        => [],
                ];
            }

            if (!isset($daygroups[$daykey]['slots'][$slotkey])) {
                $daygroups[$daykey]['slots'][$slotkey] = [
                    'timeformatted'     => userdate($ts, $timeformat),
                    'timekey'           => $ts,
                    'ispast'            => $ts < $now,
                    // Slot button: shifts all entries at exactly this timestamp.
                    'shiftsloturl'      => $this->shift_url('slot', $ts),
                    // Following button: shifts all entries at this timestamp and later.
                    'shiftfollowingurl' => $this->shift_url('following', $ts),
                    'entries'           => [],
                ];
            }

            $modname = (string) ($event['modname'] ?? '');
            if ($modname !== '') {
                $aurl = new moodle_url('/mod/' . $modname . '/view.php');
                $aurl->param('id', (int) ($event['cmid'] ?? 0));
                $activityurl = $aurl->out(false);
            } else {
                $activityurl = $timelineurlstr;
            }

            $daygroups[$daykey]['slots'][$slotkey]['entries'][] = [
                'cmid'          => (int) ($event['cmid'] ?? 0),
                'name'          => (string) ($event['cmname'] ?? ''),
                'modname'       => $modname,
                'field'         => (string) ($event['eventlabel'] ?? ''),
                // Entry button: shifts only this CM + this specific field.
                'activityurl'   => $activityurl,
                'shiftentryurl' => $this->shift_url(
                    'entry',
                    0,
                    (int) ($event['cmid'] ?? 0),
                    (string) ($event['eventtype'] ?? '')
                ),
            ];
        }

        // Flatten slots arrays to indexed lists.
        $days = [];
        foreach ($daygroups as $day) {
            ksort($day['slots']);
            $day['slots'] = array_values($day['slots']);
            $days[] = $day;
        }

        $shown      = count($this->events);
        $istruncated = $shown < $this->totalcount;

        return [
            'hasdays'             => !empty($days),
            'days'                => $days,
            'istruncated'         => $istruncated,
            'shown'               => $shown,
            'total'               => $this->totalcount,
            'timelineurl'         => $timelineurlstr,
            'opentimelinelabel'   => get_string('open_timeline', 'block_coursectrldates'),
            'viewalllabel'        => get_string('view_all_in_timeline', 'block_coursectrldates'),
            'noeventsmessage'     => $this->noeventsmessage,
            'shiftslotlabel'      => get_string('shift_slot', 'block_coursectrldates'),
            'shiftfollowinglabel' => get_string('shift_following', 'block_coursectrldates'),
            'shiftentrylabel'     => get_string('shift_entry', 'block_coursectrldates'),
        ];
    }
}
