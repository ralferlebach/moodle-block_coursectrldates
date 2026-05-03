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
 * Main block content renderable for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\output;

use block_coursectrldates\local\config_reader;
use local_coursectrl\local\analysis\calendar_grid_builder;
use local_coursectrl\local\analysis\date_collector;
use local_coursectrl\local\inventory\inventory_service;
use local_coursectrl\manager\calendar_manager;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Builds the full block content context for block_content.mustache.
 *
 * Mirrors the data flow of local_coursectrl\output\timeline_page:
 *   1. inventory_service → cm_items
 *   2. date_collector    → all date entries, chronologically sorted
 *   3. calendar_grid_builder → month grid for the mini calendar
 *   4. entries grouped by day → slot → entry (same structure as timeline)
 *
 * The template reuses the same CSS classes and button structure as
 * local_coursectrl/timeline, and initialises local_coursectrl/timeline
 * AMD module so shift buttons work identically.
 */
class block_content implements renderable, templatable {
    /** @var int Course ID. */
    private int $courseid;

    /** @var config_reader Block configuration. */
    private config_reader $config;

    /**
     * Constructor.
     *
     * @param int           $courseid Course ID.
     * @param config_reader $config   Per-instance block configuration.
     */
    public function __construct(int $courseid, config_reader $config) {
        $this->courseid = $courseid;
        $this->config = $config;
    }

    /**
     * Export template context for block_content.mustache.
     *
     * @param renderer_base $output Renderer instance.
     * @return array Template context.
     */
    public function export_for_template(renderer_base $output): array {
        $now = time();

        $snapshot = (new inventory_service())->build_for_course($this->courseid);
        $allentries = (new date_collector())->collect($snapshot->cms);

        // Calendar covers the full course date range with all entries.
        $calman = new calendar_manager();
        $months = [];
        if ($this->config->show_calendar()) {
            $months = (new calendar_grid_builder())->build(
                (int) $snapshot->course->startdate,
                $snapshot->course->enddate,
                $allentries,
                $now,
                $calman
            );
        }

        // Filter entries to the configured window.
        if ($this->config->list_mode() === config_reader::MODE_COUNT) {
            $timeto = $now + 52 * WEEKSECS;
        } else {
            $timeto = $now + ($this->config->list_weeks() * WEEKSECS);
        }

        $filtered = [];
        foreach ($allentries as $entry) {
            $ts = (int) $entry['timestamp'];
            if ($ts >= $now && $ts < $timeto) {
                $filtered[] = $entry;
            }
        }

        if ($this->config->list_mode() === config_reader::MODE_COUNT) {
            $filtered = array_slice($filtered, 0, $this->config->list_count());
        }

        $days = $this->build_days($filtered, $now);

        $timelineurl = new moodle_url('/local/coursectrl/timeline.php');
        $timelineurl->param('courseid', $this->courseid);
        $shifturl = new moodle_url('/local/coursectrl/shift.php');
        $shifturl->param('courseid', $this->courseid);

        return [
            'courseid'         => $this->courseid,
            'sesskey'          => sesskey(),
            'showcalendar'     => $this->config->show_calendar(),
            'hascalendar'      => !empty($months),
            'months'           => $months,
            'hasdays'          => !empty($days),
            'days'             => $days,
            'shifturl'         => $shifturl->out(false),
            'timelineurl'      => $timelineurl->out(false),
            'opentimelinelabel' => get_string('open_timeline', 'block_coursectrldates'),
        ];
    }

    /**
     * Group date entries into the day → slot → entry structure used by the timeline.
     *
     * @param array $entries Chronologically sorted date entries from date_collector.
     * @param int   $now     Reference timestamp for the ispast flag.
     * @return array Day blocks ready for the Mustache template.
     */
    private function build_days(array $entries, int $now): array {
        $dayformat = get_string('strftimedaydate', 'core_langconfig');
        $timeformat = get_string('strftimetime24', 'core_langconfig');

        $daygroups = [];
        foreach ($entries as $entry) {
            $ts = (int) $entry['timestamp'];
            $daykey = date('Y-m-d', $ts);
            if (!isset($daygroups[$daykey])) {
                $daygroups[$daykey] = [
                    'daykey'       => $daykey,
                    'dayformatted' => userdate($ts, $dayformat),
                    'ispast'       => false,
                    'slots'        => [],
                ];
            }
            $timekey = $ts;
            if (!isset($daygroups[$daykey]['slots'][$timekey])) {
                $daygroups[$daykey]['slots'][$timekey] = [
                    'timekey'       => $timekey,
                    'timeformatted' => userdate($timekey, $timeformat),
                    'ispast'        => $timekey < $now,
                    'entries'       => [],
                ];
            }
            $modname = (string) ($entry['modname'] ?? '');
            $activityurl = new moodle_url('/mod/' . $modname . '/view.php');
            $activityurl->param('id', $entry['cmid']);
            $deletable = in_array(
                $entry['source'] ?? '',
                ['adapter', 'cm', 'availability'],
                true
            );
            $daygroups[$daykey]['slots'][$timekey]['entries'][] = [
                'cmid'        => (int) $entry['cmid'],
                'name'        => (string) $entry['name'],
                'modname'     => $modname,
                'field'       => (string) $entry['fieldlabel'],
                'rawfield'    => (string) $entry['field'],
                'activityurl' => $activityurl->out(false),
                'deletable'   => $deletable,
            ];
        }

        $days = [];
        ksort($daygroups);
        foreach ($daygroups as $day) {
            ksort($day['slots']);
            $slots = array_values($day['slots']);
            $day['slots'] = $slots;
            $day['slotcount'] = count($slots);
            $ispast = !empty($slots) && (bool) end($slots)['ispast'];
            $day['ispast'] = $ispast;
            $days[] = $day;
        }
        return $days;
    }
}
