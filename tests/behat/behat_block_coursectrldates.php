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
 * Custom Behat step definitions for block_coursectrldates.
 *
 * @package    block_coursectrldates
 * @category   test
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL check in Behat step-definition files.

use Behat\Mink\Exception\ExpectationException;

/**
 * Step definitions for block_coursectrldates.
 */
class behat_block_coursectrldates extends behat_base {
    /**
     * Add the Course dates block to a course programmatically.
     *
     * Faster than driving the block drawer through the UI.
     *
     * @Given the course dates block is added to the :shortname course
     * @param string $shortname Course shortname.
     * @return void
     */
    public function the_course_dates_block_is_added_to_the_course(string $shortname): void {
        global $DB;

        $course  = $DB->get_record('course', ['shortname' => $shortname], '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        if ($DB->record_exists('block_instances', ['blockname' => 'coursectrldates', 'parentcontextid' => $context->id])) {
            return;
        }

        $instance = (object) [
            'blockname'         => 'coursectrldates',
            'parentcontextid'   => $context->id,
            'showinsubcontexts' => 0,
            'pagetypepattern'   => 'course-view-*',
            'subpagepattern'    => null,
            'defaultregion'     => 'side-pre',
            'defaultweight'     => 0,
            'configdata'        => '',
            'timecreated'       => time(),
            'timemodified'      => time(),
        ];
        $DB->insert_record('block_instances', $instance);

        rebuild_course_cache($course->id, true);
    }

    /**
     * Assert that the event list is present in the page (teacher view).
     *
     * @Then the course dates event list should be visible
     * @return void
     */
    public function the_course_dates_event_list_should_be_visible(): void {
        $selector = '[data-region="coursectrldates-eventlist"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                'Course dates event list not found on page.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert that the event list is NOT present in the page (student view).
     *
     * @Then the course dates event list should not be visible
     * @return void
     */
    public function the_course_dates_event_list_should_not_be_visible(): void {
        $selector = '[data-region="coursectrldates-eventlist"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if ($node) {
            throw new ExpectationException(
                'Course dates event list was found but should not be visible.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert that the setup-help notification card is present.
     *
     * @Then the setup help notification should be visible
     * @return void
     */
    public function the_setup_help_notification_should_be_visible(): void {
        $selector = '[data-region="coursectrldates-splash"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                'Setup-help notification not found on page.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert that the setup-help notification card is NOT present.
     *
     * @Then the setup help notification should not be visible
     * @return void
     */
    public function the_setup_help_notification_should_not_be_visible(): void {
        $selector = '[data-region="coursectrldates-splash"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if ($node && $node->isVisible()) {
            throw new ExpectationException(
                'Setup-help notification is visible but should not be.',
                $this->getSession()
            );
        }
    }

    /**
     * Click the defer button (Später) on the setup-help notification.
     *
     * This removes the card from the DOM without a server call.
     *
     * @When I defer the setup help notification
     * @return void
     */
    public function i_defer_the_setup_help_notification(): void {
        $selector = '[data-action="defer-help"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                'Defer button (Später) not found on the setup-help notification.',
                $this->getSession()
            );
        }
        $node->click();
    }

    /**
     * Click the permanent dismiss button (Nein) on the setup-help notification.
     *
     * Triggers an AJAX call to persist the dismissed preference.
     *
     * @When I permanently dismiss the setup help notification
     * @return void
     */
    public function i_permanently_dismiss_the_setup_help_notification(): void {
        $selector = '[data-action="dismiss-help"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                'Dismiss button (Nein) not found on the setup-help notification.',
                $this->getSession()
            );
        }
        $node->click();
        // Allow the AJAX dismiss request to complete.
        $this->getSession()->wait(2000, 'document.readyState === "complete"');
    }
}
