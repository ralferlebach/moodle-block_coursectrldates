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

namespace block_coursectrldates\local;

/**
 * Manages the per-user, per-block-instance splash-screen state.
 *
 * The splash screen is shown once when a new-start event is detected
 * (e.g. course import, reset) and is dismissed by the user. The dismissed
 * state is stored as a Moodle user preference so that it survives sessions.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Splash-screen state manager.
 */
class splash_state {

    /** @var string Preference key prefix for the dismissed state. */
    private const PREF_PREFIX = 'block_coursectrldates_splash_dismissed_';

    /** @var int Block instance ID. */
    private int $instanceid;

    /** @var int User ID. */
    private int $userid;

    /**
     * Constructor.
     *
     * @param int $instanceid Block instance ID.
     * @param int $userid     Moodle user ID.
     */
    public function __construct(int $instanceid, int $userid) {
        $this->instanceid = $instanceid;
        $this->userid = $userid;
    }

    /**
     * Whether the splash screen has already been dismissed by this user.
     *
     * @return bool
     */
    public function is_dismissed(): bool {
        $pref = get_user_preferences($this->pref_key(), 0, $this->userid);
        return (bool) $pref;
    }

    /**
     * Mark the splash screen as dismissed for this user and instance.
     *
     * @return void
     */
    public function dismiss(): void {
        set_user_preference($this->pref_key(), 1, $this->userid);
    }

    /**
     * Reset the dismissed state so the splash reappears on the next load.
     *
     * @return void
     */
    public function reset(): void {
        unset_user_preference($this->pref_key(), $this->userid);
    }

    /**
     * Build the user-preference key for this instance.
     *
     * @return string
     */
    private function pref_key(): string {
        return self::PREF_PREFIX . $this->instanceid;
    }
}
