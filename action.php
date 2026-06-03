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
 * Action endpoint for block_coursectrldates.
 *
 * Handles the reset_help action triggered from the block configuration form.
 * All AJAX-style splash actions (dismiss_help, disable_help) are handled by
 * the external service block_coursectrldates_block_action instead.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$instanceid = required_param('instanceid', PARAM_INT);
$courseid   = required_param('courseid', PARAM_INT);

require_sesskey();

// Verify that the block instance actually belongs to the given course.
$blockcontext  = context_block::instance($instanceid, MUST_EXIST);
$coursecontext = $blockcontext->get_course_context(false);
if (!$coursecontext || (int) $coursecontext->instanceid !== $courseid) {
    throw new moodle_exception('invalidcourseid');
}

$course = get_course($courseid);
require_login($course, false, null, false, true);
require_capability('block/coursectrldates:addinstance', $blockcontext);

// Re-enable show_help in block configdata and set force-show preference.
$record = $DB->get_record(
    'block_instances',
    ['id' => $instanceid],
    'id, configdata',
    MUST_EXIST
);
// phpcs:disable moodle.PHP.ForbiddenFunctions.Found -- Block configdata is written by Moodle, not user input.
$config = !empty($record->configdata)
    ? unserialize(
        base64_decode($record->configdata),
        ['allowed_classes' => [stdClass::class]]
    )
    : null;
// phpcs:enable moodle.PHP.ForbiddenFunctions.Found
if (!is_object($config)) {
    $config = new stdClass();
}
$config->show_help = '1';
$DB->set_field(
    'block_instances',
    'configdata',
    base64_encode(serialize($config)),
    ['id' => $instanceid]
);

$state = new block_coursectrldates\local\splash_state($instanceid, $USER->id);
$state->reset();
$state->force();

$redirectparam = optional_param('returnurl', '', PARAM_LOCALURL);
$returnurl = $redirectparam
    ? new moodle_url($redirectparam)
    : new moodle_url('/course/view.php', ['id' => $courseid]);
redirect($returnurl);
