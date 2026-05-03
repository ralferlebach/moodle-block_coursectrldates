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

use plugin_renderer_base;

/**
 * Renderer for block_coursectrldates.
 *
 * Thin wrapper around plugin_renderer_base that delegates all rendering
 * to Mustache templates via export_for_template().
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block renderer class.
 */
class block_renderer extends plugin_renderer_base {

    /**
     * Render the calendar section.
     *
     * @param calendar_view $view Calendar renderable.
     * @return string Rendered HTML.
     */
    public function render_calendar_view(calendar_view $view): string {
        return $this->render_from_template(
            'block_coursectrldates/calendar',
            $view->export_for_template($this)
        );
    }

    /**
     * Render the event list section.
     *
     * @param event_list $list Event list renderable.
     * @return string Rendered HTML.
     */
    public function render_event_list(event_list $list): string {
        return $this->render_from_template(
            'block_coursectrldates/event_list',
            $list->export_for_template($this)
        );
    }

    /**
     * Render the splash screen.
     *
     * @param splash_view $splash Splash renderable.
     * @return string Rendered HTML.
     */
    public function render_splash_view(splash_view $splash): string {
        return $this->render_from_template(
            'block_coursectrldates/splash',
            $splash->export_for_template($this)
        );
    }
}
