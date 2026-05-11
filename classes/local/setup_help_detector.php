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
 * Setup-help trigger detector for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\local;

/**
 * Evaluates whether any setup-help trigger fires for a given course.
 *
 * Extracted from block_coursectrldates::get_content() to allow unit testing
 * of the three trigger conditions independently.
 */
class setup_help_detector {
    /**
     * Return true if at least one configured trigger fires.
     *
     * Checks (in order, short-circuits on first match):
     *   1. Course was newly created or imported within the time window.
     *   2. Course was reset within the time window (logstore_standard_log).
     *   3. One or more course modules with time dependencies were added
     *      within the time window.
     *
     * @param int         $courseid Course ID.
     * @param config_reader $config   Block configuration.
     * @param array       $cms      Map of cmid => cm_item for this course.
     * @return bool
     */
    public function should_show(int $courseid, config_reader $config, array $cms): bool {
        global $DB;

        $winstart = time() - ($config->help_window_weeks() * WEEKSECS);

        if ($config->help_trigger_new()) {
            $timecreated = (int) $DB->get_field('course', 'timecreated', ['id' => $courseid]);
            if ($timecreated >= $winstart) {
                return true;
            }
        }

        if ($config->help_trigger_reset()) {
            $logtable = 'logstore_standard_log';
            if ($DB->get_manager()->table_exists($logtable)) {
                $sql = 'courseid = :cid AND component = :comp'
                    . ' AND action = :act AND target = :tgt AND timecreated >= :ts';
                $count = $DB->count_records_select(
                    $logtable,
                    $sql,
                    [
                        'cid'  => $courseid,
                        'comp' => 'core',
                        'act'  => 'reset',
                        'tgt'  => 'course',
                        'ts'   => $winstart,
                    ]
                );
                if ($count > 0) {
                    return true;
                }
            }
        }

        if ($config->help_trigger_timedeps() && !empty($cms)) {
            $cmids = array_keys($cms);
            [$insql, $inparams] = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
            $inparams['winstart'] = $winstart;
            $count = $DB->count_records_select(
                'course_modules',
                "id {$insql} AND added >= :winstart",
                $inparams
            );
            if ($count > 0) {
                return true;
            }
        }

        return false;
    }
}
