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
 * External service for block_coursectrldates splash actions.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\external;

use block_coursectrldates\local\splash_state;
use context_block;
use external_api;
use external_function_parameters;
use external_value;

/**
 * External API for dismissing or disabling the Termin-Assistent splash.
 *
 * Replaces the former action.php AJAX path. Registered in db/services.php
 * with ajax => true so it can be called via core/ajax from AMD JavaScript.
 *
 * Supported actions:
 *   dismiss_help  — dismisses the splash for the current user (user preference).
 *   disable_help  — disables the Termin-Assistent for all users (instance config).
 */
class block_action extends external_api {
    /**
     * Declare the parameters accepted by execute().
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'instanceid' => new external_value(PARAM_INT, 'Block instance ID'),
            'courseid'   => new external_value(PARAM_INT, 'Course ID'),
            'action'     => new external_value(PARAM_ALPHANUMEXT, 'Action name'),
        ]);
    }

    /**
     * Execute the requested splash action.
     *
     * @param int    $instanceid Block instance ID.
     * @param int    $courseid   Course ID.
     * @param string $action     One of: dismiss_help, disable_help.
     * @return bool True on success.
     */
    public static function execute(int $instanceid, int $courseid, string $action): bool {
        global $USER;

        [
            'instanceid' => $instanceid,
            'courseid'   => $courseid,
            'action'     => $action,
        ] = self::validate_parameters(
            self::execute_parameters(),
            [
                'instanceid' => $instanceid,
                'courseid'   => $courseid,
                'action'     => $action,
            ]
        );

        $blockcontext = context_block::instance($instanceid, MUST_EXIST);

        // Verify the block instance actually belongs to the given course.
        $coursecontext = $blockcontext->get_course_context(false);
        if (!$coursecontext || (int) $coursecontext->instanceid !== $courseid) {
            throw new \moodle_exception('invalidcourseid');
        }

        self::validate_context($blockcontext);
        require_capability('block/coursectrldates:view', $blockcontext);

        switch ($action) {
            case 'dismiss_help':
                $state = new splash_state($instanceid, $USER->id);
                $state->dismiss();
                break;

            case 'disable_help':
                require_capability('block/coursectrldates:addinstance', $blockcontext);
                $state = new splash_state($instanceid, $USER->id);
                $state->dismiss();
                self::set_show_help($instanceid, false);
                break;

            default:
                throw new \moodle_exception('invalidaction', 'error');
        }

        return true;
    }

    /**
     * Declare the return type of execute().
     *
     * @return external_value
     */
    public static function execute_returns(): external_value {
        return new external_value(PARAM_BOOL, 'True on success');
    }

    /**
     * Update the show_help config flag for a block instance.
     *
     * Writes directly to block_instances.configdata so the change takes
     * effect on the next page load without requiring a form submission.
     *
     * @param int  $instanceid Block instance ID.
     * @param bool $enable     True to enable the Termin-Assistent, false to disable.
     * @return void
     */
    private static function set_show_help(int $instanceid, bool $enable): void {
        global $DB;

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
                ['allowed_classes' => ['\stdClass']]
            )
            : null;
        // phpcs:enable moodle.PHP.ForbiddenFunctions.Found
        if (!is_object($config)) {
            $config = new \stdClass();
        }
        $config->show_help = $enable ? '1' : '0';
        $DB->set_field(
            'block_instances',
            'configdata',
            base64_encode(serialize($config)),
            ['id' => $instanceid]
        );
    }
}
