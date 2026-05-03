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
 * Block class for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Course dates block.
 *
 * Renders a mini calendar and a chronological list of upcoming activity
 * dates. A single call to local_coursectrl's inventory and date-collector
 * services feeds both sections so the DB is hit only once per page load.
 */
class block_coursectrldates extends block_base {
    /**
     * Initialise the block title.
     *
     * @return void
     */
    public function init(): void {
        $this->title = get_string('pluginname', 'block_coursectrldates');
    }

    /**
     * Return false – no site-level configuration form.
     *
     * @return bool
     */
    public function has_config(): bool {
        return false;
    }

    /**
     * Return true – per-instance configuration is supported.
     *
     * @return bool
     */
    public function instance_allow_config(): bool {
        return true;
    }

    /**
     * Restrict this block to course pages only.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return [
            'course-view' => true,
            'site'        => false,
            'my'          => false,
        ];
    }

    /**
     * Produce the block content.
     *
     * A single inventory pass via local_coursectrl's inventory_service and
     * date_collector feeds both the calendar grid and the event list. The
     * result is rendered via block_coursectrldates/block (which includes
     * the calendar and event_list partials).
     *
     * @return stdClass|null Block content object, or null when not applicable.
     */
    public function get_content(): ?stdClass {
        global $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $context = $this->context;
        if ($context->contextlevel !== CONTEXT_BLOCK) {
            $this->content->text = '';
            return $this->content;
        }

        $coursecontext = $context->get_parent_context();
        if (!$coursecontext || $coursecontext->contextlevel !== CONTEXT_COURSE) {
            $this->content->text = '';
            return $this->content;
        }

        if (!has_capability('block/coursectrldates:view', $context)) {
            $this->content->text = '';
            return $this->content;
        }

        $courseid = (int) $coursecontext->instanceid;
        $config = new \block_coursectrldates\local\config_reader($this->config ?? null);
        $now = time();

        // Single inventory pass shared by calendar and event list.
        $snapshot = (new \local_coursectrl\local\inventory\inventory_service())
            ->build_for_course($courseid);
        $allentries = (new \local_coursectrl\local\analysis\date_collector())
            ->collect($snapshot->cms);

        // Calendar: build month grid over the full course range.
        $months = [];
        if ($config->show_calendar()) {
            $months = (new \local_coursectrl\local\analysis\calendar_grid_builder())->build(
                (int) $snapshot->course->startdate,
                $snapshot->course->enddate,
                $allentries,
                $now,
                new \local_coursectrl\manager\calendar_manager()
            );
        }

        // Event list: filter allentries by configured mode.
        if ($config->list_mode() === \block_coursectrldates\local\config_reader::MODE_COUNT) {
            $futureentries = array_values(array_filter(
                $allentries,
                static function (array $e) use ($now): bool {
                    return (int) $e['timestamp'] >= $now;
                }
            ));
            $total = count($futureentries);
            $showentries = array_slice($futureentries, 0, $config->list_count());
            $noeventsmessage = get_string('no_events_count', 'block_coursectrldates');
        } else {
            $weeks = $config->list_weeks();
            $timeto = $now + ($weeks * WEEKSECS);
            $showentries = array_values(array_filter(
                $allentries,
                static function (array $e) use ($now, $timeto): bool {
                    $ts = (int) $e['timestamp'];
                    return $ts >= $now && $ts < $timeto;
                }
            ));
            $total = count($showentries);
            $noeventsmessage = get_string('no_events', 'block_coursectrldates', $weeks);
        }

        $events = array_map(
            static function (array $e): array {
                return [
                    'timestamp'  => (int) $e['timestamp'],
                    'cmid'       => (int) $e['cmid'],
                    'cmname'     => (string) $e['name'],
                    'modname'    => (string) $e['modname'],
                    'eventtype'  => (string) $e['field'],
                    'eventlabel' => (string) $e['fieldlabel'],
                ];
            },
            $showentries
        );

        $eventlist = new \block_coursectrldates\output\event_list(
            $events,
            $total,
            $courseid,
            $noeventsmessage
        );

        // Merge calendar and event-list context into the block template.
        $data = $eventlist->export_for_template($OUTPUT);
        $data['showsplash']   = false;
        $data['showcalendar'] = $config->show_calendar() && !empty($months);
        $data['hascalendar']  = !empty($months);
        $data['months']       = $months;

        $this->content->text = $OUTPUT->render_from_template(
            'block_coursectrldates/block',
            $data
        );

        return $this->content;
    }

    /**
     * Prevent multiple instances in one course.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }
}
