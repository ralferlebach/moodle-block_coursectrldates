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
 * Privacy API implementation for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates\privacy;

use block_coursectrldates\local\splash_state;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\user_preference_provider;
use core_privacy\local\request\writer;

/**
 * Privacy provider for block_coursectrldates.
 *
 * The block stores one type of personal data: a per-user, per-instance
 * User Preference that records whether the setup-help notification has been
 * permanently dismissed. All preferences share the prefix defined in
 * {@see splash_state::PREF_PREFIX}.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    user_preference_provider {
    /**
     * Describe the personal data stored by this plugin.
     *
     * @param collection $collection Metadata collection to populate.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference(
            splash_state::PREF_PREFIX,
            'privacy:metadata:preference:splash_dismissed'
        );
        return $collection;
    }

    /**
     * Export all User Preferences belonging to the given user.
     *
     * Each dismissed block instance is exported as a separate preference entry.
     *
     * @param int $userid Moodle user ID.
     * @return void
     */
    public static function export_user_preferences(int $userid): void {
        global $DB;

        $like = $DB->sql_like('name', ':prefix', false, false);
        $escapedprefix = $DB->sql_like_escape(splash_state::PREF_PREFIX);
        $params = [
            'userid' => $userid,
            'prefix' => $escapedprefix . '%',
        ];
        $prefs = $DB->get_records_select(
            'user_preferences',
            "userid = :userid AND {$like}",
            $params
        );

        foreach ($prefs as $pref) {
            writer::export_user_preference(
                'block_coursectrldates',
                $pref->name,
                $pref->value,
                get_string(
                    'privacy:metadata:preference:splash_dismissed',
                    'block_coursectrldates'
                )
            );
        }
    }
}
