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
 * Renders a compact course-date overview on the course page and provides
 * entry points into the date-management features of local_coursectrl.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Course dates block.
 *
 * Displays the next upcoming dates for a course and offers shortcut
 * links into the timeline and bulk-date features of local_coursectrl.
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
     * Return true – this block has a configuration form.
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
     * @return stdClass|null Block content object, or null when not applicable.
     */
    public function get_content(): ?stdClass {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        // Require course context.
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

        // Stub: real rendering will be added in a later phase.
        $this->content->text = get_string('comingsoon', 'block_coursectrldates');

        return $this->content;
    }

    /**
     * Allow multiple instances of this block in one course.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }
}
