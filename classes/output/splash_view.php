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
 * Splash screen renderable for block_coursectrldates.
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
 * Renderable for the splash screen shown on new-start events.
 *
 * The splash prompts the teacher to review course dates when the course
 * has been newly created, reset, or has newly imported time-limited content.
 */
class splash_view implements renderable, templatable {
    /** @var int Course ID used for building timeline URL. */
    private int $courseid;

    /** @var int Block instance ID for the dismiss action. */
    private int $instanceid;

    /**
     * Constructor.
     *
     * @param int $courseid   Course ID.
     * @param int $instanceid Block instance ID.
     */
    public function __construct(int $courseid, int $instanceid) {
        $this->courseid = $courseid;
        $this->instanceid = $instanceid;
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output Renderer instance.
     * @return array Template context.
     */
    public function export_for_template(renderer_base $output): array {
        $timelineurl = new moodle_url('/local/coursectrl/timeline.php');
        $timelineurl->param('courseid', $this->courseid);

        $dismissurl = new moodle_url('/blocks/coursectrldates/action.php');
        $dismissurl->param('action', 'dismiss_splash');
        $dismissurl->param('instanceid', $this->instanceid);
        $dismissurl->param('sesskey', sesskey());

        return [
            'title'         => get_string('splash_title', 'block_coursectrldates'),
            'message'       => get_string('splash_message', 'block_coursectrldates'),
            'timelineurl'   => $timelineurl->out(false),
            'timelinelabel' => get_string('open_timeline', 'block_coursectrldates'),
            'dismissurl'    => $dismissurl->out(false),
            'dismisslabel'  => get_string('splash_dismiss', 'block_coursectrldates'),
        ];
    }
}
