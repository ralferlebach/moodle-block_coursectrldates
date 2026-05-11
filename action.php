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

switch ($action) {
    case 'dismiss_help':
    case 'dismiss_splash':
        // Accept both names for backward compatibility.
        $state = new block_coursectrldates\local\splash_state($instanceid, $USER->id);
        $state->dismiss();
        break;

    default:
        throw new moodle_exception('invalidaction', 'error');
}

if ($isajax) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    exit;
}

$returnurl = new moodle_url('/course/view.php', ['id' => $courseid]);
redirect($returnurl);
