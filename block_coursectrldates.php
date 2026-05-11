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
 *
 * The course ID is resolved via $this->page->context so the block works
 * both on the course view page and on local_coursectrl management pages
 * (timeline, manage, shift, etc.) that set $PAGE->context = context_course.
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
     * Return the page formats on which this block may appear.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return [
            'course-view'        => true,
            'local-coursectrl-*' => true,
            'site'               => false,
            'my'                 => false,
        ];
    }

    /**
     * Intercept config save to handle the reset-help option.
     *
     * @param stdClass $data Form data.
     * @param bool     $nolongerused Legacy parameter (unused).
     * @return stdClass
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $USER;

        if (!empty($data->config_reset_help)) {
            $state = new \block_coursectrldates\local\splash_state(
                $this->instance->id,
                $USER->id
            );
            $state->reset();
            $data->config_reset_help = 0;
        }

        return parent::instance_config_save($data, $nolongerused);
    }

    /**
     * Produce the block content.
     *
     * The course ID is derived from $this->page->context so the block
     * works correctly on both course-view pages and CCH pages (which call
     * $PAGE->set_context with the course context and $PAGE->set_pagetype
     * to course-view-* so the block instance is matched).
     *
     * @return stdClass|null
     */
    public function get_content(): ?stdClass {
        global $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        // Resolve course context via $this->page (Moodle-approved in blocks).
        $coursecontext = $this->page->context;
        if ($coursecontext->contextlevel !== CONTEXT_COURSE) {
            $coursecontext = $coursecontext->get_course_context(false);
        }
        if (!$coursecontext) {
            $this->content->text = '';
            return $this->content;
        }

        if (!has_capability('block/coursectrldates:view', $this->context)) {
            $this->content->text = '';
            return $this->content;
        }

        $courseid = (int) $coursecontext->instanceid;
        $config   = new \block_coursectrldates\local\config_reader($this->config ?? null);
        $now      = time();

        // Single inventory pass shared by calendar and event list.
        $snapshot   = (new \local_coursectrl\local\inventory\inventory_service())
            ->build_for_course($courseid);
        $allentries = (new \local_coursectrl\local\analysis\date_collector())
            ->collect($snapshot->cms);

        // Calendar: full course range, all entries.
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

        // Event list: upcoming events only (timestamp >= now).
        if ($config->list_mode() === \block_coursectrldates\local\config_reader::MODE_COUNT) {
            $timeto = $now + (52 * WEEKSECS);
            $noeventsmessage = get_string('no_events_count', 'block_coursectrldates');
        } else {
            $weeks  = $config->list_weeks();
            $timeto = $now + ($weeks * WEEKSECS);
            $noeventsmessage = get_string('no_events', 'block_coursectrldates', $weeks);
        }

        $futureentries = [];
        foreach ($allentries as $entry) {
            $ts = (int) $entry['timestamp'];
            if ($ts >= $now && $ts < $timeto) {
                $futureentries[] = $entry;
            }
        }

        $total = count($futureentries);
        if ($config->list_mode() === \block_coursectrldates\local\config_reader::MODE_COUNT) {
            $showentries = array_slice($futureentries, 0, $config->list_count());
        } else {
            $showentries = $futureentries;
        }

        $events = [];
        foreach ($showentries as $e) {
            $events[] = [
                'timestamp'  => (int) $e['timestamp'],
                'cmid'       => (int) $e['cmid'],
                'cmname'     => (string) $e['name'],
                'modname'    => (string) $e['modname'],
                'eventtype'  => (string) $e['field'],
                'eventlabel' => (string) $e['fieldlabel'],
            ];
        }

        $eventlist = new \block_coursectrldates\output\event_list(
            $events,
            $total,
            $courseid,
            $noeventsmessage
        );

        // Setup-help: evaluate triggers and dismissed state.
        $showhelp = false;
        $helpdata = null;
        if ($config->show_help()) {
            $splashstate = new \block_coursectrldates\local\splash_state(
                $this->instance->id,
                $USER->id
            );
            if (
                !$splashstate->is_dismissed()
                && (new \block_coursectrldates\local\setup_help_detector())
                    ->should_show($courseid, $config, $snapshot->cms)
            ) {
                $showhelp = true;

                $dismissurl = new \moodle_url('/blocks/coursectrldates/action.php');
                $dismissurl->param('action', 'dismiss_help');
                $dismissurl->param('instanceid', $this->instance->id);
                $dismissurl->param('courseid', $courseid);
                $dismissurl->param('sesskey', sesskey());

                $timelineurl = new \moodle_url('/local/coursectrl/timeline.php');
                $timelineurl->param('courseid', $courseid);

                $helpdata = [
                    'question'    => get_string('help_question', 'block_coursectrldates'),
                    'timelineurl' => $timelineurl->out(false),
                    'dismissurl'  => $dismissurl->out(false),
                    'label_yes'   => get_string('help_yes', 'block_coursectrldates'),
                    'label_later' => get_string('help_later', 'block_coursectrldates'),
                    'label_no'    => get_string('help_no', 'block_coursectrldates'),
                ];
            }
        }

        $data = $eventlist->export_for_template($OUTPUT);
        $data['showhelp']     = $showhelp;
        $data['helpdata']     = $helpdata;
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
