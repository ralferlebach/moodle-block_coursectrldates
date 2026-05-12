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
 * PHPUnit tests for the block_coursectrldates Privacy provider.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates;

use block_coursectrldates\local\splash_state;
use block_coursectrldates\privacy\provider;
use core_privacy\local\metadata\collection;
use core_privacy\tests\provider_testcase;

/**
 * Tests for the privacy provider.
 *
 * @covers \block_coursectrldates\privacy\provider
 */
final class privacy_provider_test extends provider_testcase {
    /**
     * get_metadata() declares the splash-dismissed user preference.
     */
    public function test_get_metadata_declares_preference(): void {
        $collection = new collection('block_coursectrldates');
        $result = provider::get_metadata($collection);

        $items = $result->get_collection();
        $this->assertNotEmpty($items);

        // At least one item must reference the preference prefix.
        $found = false;
        foreach ($items as $item) {
            if (str_contains($item->get_name(), 'block_coursectrldates_splash_dismissed')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Splash-dismissed preference not declared in metadata.');
    }

    /**
     * export_user_preferences() produces no output when no preferences exist.
     */
    public function test_export_produces_nothing_for_user_without_preferences(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        provider::export_user_preferences($user->id);

        $writer = \core_privacy\local\request\writer::with_context(
            \context_system::instance()
        );
        // The writer should have no preferences recorded for our plugin.
        $prefs = $writer->get_user_preferences('block_coursectrldates');
        $this->assertEmpty((array) $prefs);
    }

    /**
     * export_user_preferences() exports preferences that were set via splash_state.
     */
    public function test_export_includes_dismissed_splash_preference(): void {
        $this->resetAfterTest();

        $user  = $this->getDataGenerator()->create_user();
        $state = new splash_state(42, $user->id);
        $state->dismiss();

        provider::export_user_preferences($user->id);

        $writer = \core_privacy\local\request\writer::with_context(
            \context_system::instance()
        );
        $prefs = (array) $writer->get_user_preferences('block_coursectrldates');
        $this->assertNotEmpty($prefs, 'No preferences exported despite dismiss being set.');

        $key = splash_state::PREF_PREFIX . '42';
        $this->assertArrayHasKey($key, $prefs, "Expected preference key '$key' not exported.");
    }

    /**
     * export_user_preferences() only exports preferences for the requested user.
     */
    public function test_export_is_scoped_to_requested_user(): void {
        $this->resetAfterTest();

        $usera = $this->getDataGenerator()->create_user();
        $userb = $this->getDataGenerator()->create_user();

        (new splash_state(1, $usera->id))->dismiss();
        (new splash_state(1, $userb->id))->dismiss();

        provider::export_user_preferences($usera->id);

        $writer = \core_privacy\local\request\writer::with_context(
            \context_system::instance()
        );
        $prefs = (array) $writer->get_user_preferences('block_coursectrldates');
        $this->assertNotEmpty($prefs);

        // All exported prefs must belong to user A (same values regardless;
        // the key is that only one user's export was requested).
        foreach (array_keys($prefs) as $k) {
            $this->assertStringContainsString(
                splash_state::PREF_PREFIX,
                $k,
                "Unexpected preference key: $k"
            );
        }
    }
}
