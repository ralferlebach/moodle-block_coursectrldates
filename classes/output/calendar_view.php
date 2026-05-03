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
 * Calendar section renderable for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Renderable for the optional mini-calendar section of the block.
 *
 * Calendar data and layout are delegated to local_coursectrl. This
 * renderable acts as the data transport between the provider and the
 * Mustache template.
 */
class calendar_view implements renderable, templatable {
    /** @var int Number of weeks to display. */
    private int $weeks;

    /** @var array Raw calendar data from local_coursectrl (stub: empty). */
    private array $calendardata;

    /**
     * Constructor.
     *
     * @param int   $weeks        Number of weeks ahead to display.
     * @param array $calendardata Pre-built calendar data from local_coursectrl.
     */
    public function __construct(int $weeks, array $calendardata = []) {
        $this->weeks = $weeks;
        $this->calendardata = $calendardata;
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output Renderer instance.
     * @return array Template context.
     */
    public function export_for_template(renderer_base $output): array {
        return [
            'weeks'        => $this->weeks,
            'calendardata' => $this->calendardata,
            'hascalendar'  => !empty($this->calendardata),
        ];
    }
}
