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

use block_coursectrldates\local\config_reader;

/**
 * Instance configuration form.
 *
 * Settings: calendar visibility, event-list mode, and the setup-help
 * (Einrichtungshilfe) feature with configurable trigger conditions.
 */
class block_coursectrldates_edit_form extends block_edit_form {
    /**
     * Add plugin-specific fields to the configuration form.
     *
     * @param MoodleQuickForm $mform The Moodle form object.
     * @return void
     */
    protected function specific_definition($mform): void {

        // Calendar section.
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
        $mform->setDefault('config_calendar_weeks', config_reader::DEFAULT_CALENDAR_WEEKS);
        $mform->hideIf('config_calendar_weeks', 'config_show_calendar', 'notchecked');

        // Event list section.
        $mform->addElement(
            'header',
            'configheader_events',
            get_string('config_events_header', 'block_coursectrldates')
        );

        $modeoptions = [
            config_reader::MODE_TIMEWINDOW => get_string(
                'config_mode_timewindow',
                'block_coursectrldates'
            ),
            config_reader::MODE_COUNT => get_string(
                'config_mode_count',
                'block_coursectrldates'
            ),
        ];
        $mform->addElement(
            'select',
            'config_list_mode',
            get_string('config_list_mode', 'block_coursectrldates'),
            $modeoptions
        );
        $mform->setDefault('config_list_mode', config_reader::MODE_TIMEWINDOW);

        $mform->addElement(
            'select',
            'config_list_weeks',
            get_string('config_list_weeks', 'block_coursectrldates'),
            $weekoptions
        );
        $mform->setDefault('config_list_weeks', config_reader::DEFAULT_LIST_WEEKS);
        $mform->hideIf('config_list_weeks', 'config_list_mode', 'neq', config_reader::MODE_TIMEWINDOW);

        $mform->addElement(
            'text',
            'config_list_count',
            get_string('config_list_count', 'block_coursectrldates'),
            ['size' => 4]
        );
        $mform->setType('config_list_count', PARAM_INT);
        $mform->setDefault('config_list_count', config_reader::DEFAULT_LIST_COUNT);
        $mform->hideIf('config_list_count', 'config_list_mode', 'neq', config_reader::MODE_COUNT);

        // Setup-help section (Einrichtungshilfe).
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

        // Time window for trigger evaluation.
        $mform->addElement(
            'select',
            'config_help_window_weeks',
            get_string('config_help_window_weeks', 'block_coursectrldates'),
            $weekoptions
        );
        $mform->setDefault('config_help_window_weeks', config_reader::DEFAULT_HELP_WEEKS);
        $mform->hideIf('config_help_window_weeks', 'config_show_help', 'notchecked');

        // Trigger checkboxes.
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

        // Manual reset: button link instead of checkbox so it acts immediately.
        $reseturl = new \moodle_url('/blocks/coursectrldates/action.php', [
            'action'     => 'reset_help',
            'instanceid' => $this->block->instance->id,
            'courseid'   => $this->block->page->course->id,
            'sesskey'    => sesskey(),
            'returnurl'  => $this->block->page->url->out_as_local_url(false),
        ]);
        $label    = get_string('config_reset_help', 'block_coursectrldates');
        $desc     = get_string('config_reset_help_desc', 'block_coursectrldates');
        $btnhtml  = '<div class="form-group row fitem">';
        $btnhtml .= '<div class="col-md-3 col-form-label d-flex pb-0 pr-md-0"></div>';
        $btnhtml .= '<div class="col-md-9 form-inline align-items-start felement">';
        $btnhtml .= '<div>';
        $btnhtml .= '<a href="' . $reseturl->out(false) . '"';
        $btnhtml .= ' class="btn btn-outline-secondary btn-sm">';
        $btnhtml .= htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $btnhtml .= '</a>';
        $btnhtml .= '<div class="form-control-feedback">' . $desc . '</div>';
        $btnhtml .= '</div></div></div>';
        $mform->addElement('html', $btnhtml);
        $mform->hideIf('config_show_help', 'config_show_help', 'notchecked');
    }
}
