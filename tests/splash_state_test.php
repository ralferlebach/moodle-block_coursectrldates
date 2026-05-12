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
 * PHPUnit tests for splash_state.
 *
 * @package    block_coursectrldates
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_coursectrldates;

use advanced_testcase;
use block_coursectrldates\local\splash_state;

/**
 * Tests for splash_state.
 *
 * @covers \block_coursectrldates\local\splash_state
 */
final class splash_state_test extends advanced_testcase {
    /**
     * Fresh instance reports not dismissed.
     */
    public function test_initial_state_is_not_dismissed(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $state = new splash_state(1, $user->id);

        $this->assertFalse($state->is_dismissed());
    }

    /**
     * After dismiss() the state is reported as dismissed.
     */
    public function test_dismiss_marks_as_dismissed(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $state = new splash_state(1, $user->id);

        $state->dismiss();

        $this->assertTrue($state->is_dismissed());
    }

    /**
     * After reset() a previously dismissed state is cleared.
     */
    public function test_reset_clears_dismissed_state(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $state = new splash_state(1, $user->id);

        $state->dismiss();
        $this->assertTrue($state->is_dismissed());

        $state->reset();
        $this->assertFalse($state->is_dismissed());
    }

    /**
     * Different instance IDs have independent dismissed states.
     */
    public function test_different_instances_are_independent(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $statea = new splash_state(1, $user->id);
        $stateb = new splash_state(2, $user->id);

        $statea->dismiss();

        $this->assertTrue($statea->is_dismissed());
        $this->assertFalse($stateb->is_dismissed());
    }

    /**
     * Different users share no dismissed state for the same instance.
     */
    public function test_different_users_are_independent(): void {
        $this->resetAfterTest();

        $usera = $this->getDataGenerator()->create_user();
        $userb = $this->getDataGenerator()->create_user();

        $statea = new splash_state(1, $usera->id);
        $stateb = new splash_state(1, $userb->id);

        $statea->dismiss();

        $this->assertTrue($statea->is_dismissed());
        $this->assertFalse($stateb->is_dismissed());
    }

    /**
     * PREF_PREFIX constant is non-empty and plugin-namespaced.
     */
    public function test_pref_prefix_is_namespaced(): void {
        $this->assertStringStartsWith(
            'block_coursectrldates_',
            splash_state::PREF_PREFIX
        );
    }

    /**
     * Multiple dismiss() calls are idempotent.
     */
    public function test_dismiss_is_idempotent(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $state = new splash_state(1, $user->id);

        $state->dismiss();
        $state->dismiss();

        $this->assertTrue($state->is_dismissed());
    }

    /**
     * Multiple reset() calls are safe even when not previously dismissed.
     */
    public function test_reset_on_fresh_state_is_safe(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $state = new splash_state(1, $user->id);

        // Should not throw.
        $state->reset();

        $this->assertFalse($state->is_dismissed());
    }
}
