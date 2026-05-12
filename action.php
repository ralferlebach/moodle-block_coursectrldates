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
 * Handles lightweight block actions (currently: dismiss_help). Supports
 * both AJAX calls (returns JSON) and direct browser navigation (redirects).
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$instanceid = required_param('instanceid', PARAM_INT);
$action     = required_param('action', PARAM_ALPHAEXT);
$courseid   = required_param('courseid', PARAM_INT);

require_sesskey();

// Verify that the block instance actually belongs to the given course.
$blockcontext = context_block::instance($instanceid, MUST_EXIST);
$coursecontext = $blockcontext->get_course_context(false);
if (!$coursecontext || (int) $coursecontext->instanceid !== $courseid) {
    throw new moodle_exception('invalidcourseid');
}

$course = get_course($courseid);
require_login($course, false, null, false, true);
require_capability('block/coursectrldates:view', $blockcontext);

$isajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/**
 * Update the show_help config flag for a block instance.
 *
 * Directly writes to block_instances.configdata so the change takes
 * effect on the next page load without requiring a form submission.
 *
 * @param int  $instanceid Block instance ID.
 * @param bool $enable     True to enable, false to disable.
 * @return void
 */
function update_block_show_help(int $instanceid, bool $enable): void {
    global $DB;
    $record = $DB->get_record(
        'block_instances',
        ['id' => $instanceid],
        'id, configdata',
        MUST_EXIST
    );
    // phpcs:disable moodle.PHP.ForbiddenFunctions.Found -- Block configdata is written by Moodle, not user input.
    $config = !empty($record->configdata)
        ? unserialize(base64_decode($record->configdata), ['allowed_classes' => [stdClass::class]])
        : null;
    // phpcs:enable moodle.PHP.ForbiddenFunctions.Found
    if (!is_object($config)) {
        $config = new stdClass();
    }
    $config->show_help = $enable ? '1' : '0';
    $DB->set_field(
        'block_instances',
        'configdata',
        base64_encode(serialize($config)),
        ['id' => $instanceid]
    );
}

switch ($action) {
    case 'dismiss_help':
    case 'dismiss_splash':
        // Accept both names for backward compatibility.
        $state = new block_coursectrldates\local\splash_state($instanceid, $USER->id);
        $state->dismiss();
        break;

    case 'disable_help':
        // Requires elevated capability: changes instance config for all users.
        require_capability('block/coursectrldates:addinstance', $blockcontext);
        $state = new block_coursectrldates\local\splash_state($instanceid, $USER->id);
        $state->dismiss();
        update_block_show_help($instanceid, false);
        break;

    case 'reset_help':
        // Requires elevated capability: changes instance config and preference.
        require_capability('block/coursectrldates:addinstance', $blockcontext);
        $state = new block_coursectrldates\local\splash_state($instanceid, $USER->id);
        $state->reset();
        $state->force();
        update_block_show_help($instanceid, true);
        break;

    default:
        throw new moodle_exception('invalidaction', 'error');
}

if ($isajax) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

$redirectparam = optional_param('returnurl', '', PARAM_LOCALURL);
$returnurl = $redirectparam
    ? new moodle_url($redirectparam)
    : new moodle_url('/course/view.php', ['id' => $courseid]);
redirect($returnurl);
