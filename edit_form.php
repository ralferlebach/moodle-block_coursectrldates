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
 * Per-instance configuration form for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// No 'use' alias — reference the class by fully-qualified name below to avoid
// any alias-resolution ambiguity when this file is included in an AJAX context.

/**
 * Instance configuration form.
 *
 * Settings: calendar visibility, event-list mode, and the Termin-Assistent
 * feature with configurable trigger conditions and a reset button.
 */
class block_coursectrldates_edit_form extends block_edit_form {
    /**
     * Add plugin-specific fields to the configuration form.
     *
     * @param MoodleQuickForm $mform The Moodle form object.
     * @return void
     */
    protected function specific_definition($mform): void {
        // Fully-qualified class alias for readability throughout this method.
        $cr = \block_coursectrldates\local\config_reader::class;

        // Calendar settings section.
        $mform->addElement(
            'header',
            'configheader_calendar',
            get_string('config_calendar_header', 'block_coursectrldates')
        );

        $mform->addElement(
            'advcheckbox',
            'config_show_calendar',
            get_string('config_show_calendar', 'block_coursectrldates')
        );
        $mform->setDefault('config_show_calendar', 1);

        $weekoptions = array_combine(range(1, 6), range(1, 6));
        $mform->addElement(
            'select',
            'config_calendar_weeks',
            get_string('config_calendar_weeks', 'block_coursectrldates'),
            $weekoptions
        );
        $mform->setDefault('config_calendar_weeks', $cr::DEFAULT_CALENDAR_WEEKS);
        $mform->hideIf('config_calendar_weeks', 'config_show_calendar', 'notchecked');

        // Event list settings section.
        $mform->addElement(
            'header',
            'configheader_events',
            get_string('config_events_header', 'block_coursectrldates')
        );

        $modeoptions = [
            $cr::MODE_TIMEWINDOW => get_string('config_mode_timewindow', 'block_coursectrldates'),
            $cr::MODE_COUNT => get_string('config_mode_count', 'block_coursectrldates'),
        ];
        $mform->addElement(
            'select',
            'config_list_mode',
            get_string('config_list_mode', 'block_coursectrldates'),
            $modeoptions
        );
        $mform->setDefault('config_list_mode', $cr::MODE_TIMEWINDOW);

        $mform->addElement(
            'select',
            'config_list_weeks',
            get_string('config_list_weeks', 'block_coursectrldates'),
            $weekoptions
        );
        $mform->setDefault('config_list_weeks', $cr::DEFAULT_LIST_WEEKS);
        $mform->hideIf('config_list_weeks', 'config_list_mode', 'neq', $cr::MODE_TIMEWINDOW);

        $mform->addElement(
            'text',
            'config_list_count',
            get_string('config_list_count', 'block_coursectrldates'),
            ['size' => 4]
        );
        $mform->setType('config_list_count', PARAM_INT);
        $mform->setDefault('config_list_count', $cr::DEFAULT_LIST_COUNT);
        $mform->hideIf('config_list_count', 'config_list_mode', 'neq', $cr::MODE_COUNT);

        // Termin-Assistent settings section.
        $mform->addElement(
            'header',
            'configheader_help',
            get_string('config_help_header', 'block_coursectrldates')
        );

        $mform->addElement(
            'advcheckbox',
            'config_show_help',
            get_string('config_show_help', 'block_coursectrldates')
        );
        $mform->setDefault('config_show_help', 1);

        $mform->addElement(
            'select',
            'config_help_window_weeks',
            get_string('config_help_window_weeks', 'block_coursectrldates'),
            $weekoptions
        );
        $mform->setDefault('config_help_window_weeks', $cr::DEFAULT_HELP_WEEKS);
        $mform->hideIf('config_help_window_weeks', 'config_show_help', 'notchecked');

        $mform->addElement(
            'advcheckbox',
            'config_help_trigger_new',
            get_string('config_help_trigger_new', 'block_coursectrldates')
        );
        $mform->setDefault('config_help_trigger_new', 1);
        $mform->hideIf('config_help_trigger_new', 'config_show_help', 'notchecked');

        $mform->addElement(
            'advcheckbox',
            'config_help_trigger_reset',
            get_string('config_help_trigger_reset', 'block_coursectrldates')
        );
        $mform->setDefault('config_help_trigger_reset', 1);
        $mform->hideIf('config_help_trigger_reset', 'config_show_help', 'notchecked');

        $mform->addElement(
            'advcheckbox',
            'config_help_trigger_timedeps',
            get_string('config_help_trigger_timedeps', 'block_coursectrldates')
        );
        $mform->setDefault('config_help_trigger_timedeps', 1);
        $mform->hideIf('config_help_trigger_timedeps', 'config_show_help', 'notchecked');

        // Reset button: navigates directly to action.php so it works immediately
        // without saving the config form. Uses a safe constructed return URL
        // instead of $this->page->url which may be null in AJAX modal context.
        $courseid   = (int) $this->page->course->id;
        $returnurl  = new \moodle_url('/course/view.php', ['id' => $courseid]);
        $reseturl   = new \moodle_url('/blocks/coursectrldates/action.php', [
            'action'     => 'reset_help',
            'instanceid' => (int) $this->block->instance->id,
            'courseid'   => $courseid,
            'sesskey'    => sesskey(),
            'returnurl'  => $returnurl->out(false),
        ]);
        $resetlabel = htmlspecialchars(
            get_string('config_reset_help', 'block_coursectrldates'),
            ENT_QUOTES,
            'UTF-8'
        );
        $resetbtn = '<a href="' . $reseturl->out(false) . '"'
            . ' class="btn btn-outline-secondary btn-sm">' . $resetlabel . '</a>';
        $mform->addElement('static', 'reset_help_display', '', $resetbtn);
        $mform->hideIf('reset_help_display', 'config_show_help', 'notchecked');
    }
}
