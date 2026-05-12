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
    // Setup helpers.

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

        if (
            $DB->record_exists(
                'block_instances',
                ['blockname' => 'coursectrldates', 'parentcontextid' => $context->id]
            )
        ) {
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
     * Directly disable the Termin-Assistent in the block's config for a course.
     *
     * Simulates the effect of clicking "Abschalten" without going through the UI,
     * so the state can be set up as a Behat Given precondition.
     *
     * @Given the Termin-Assistent is disabled in the block config for :shortname
     * @param string $shortname Course shortname.
     * @return void
     */
    public function the_termin_assistent_is_disabled_in_the_block_config_for(string $shortname): void {
        global $DB;

        $course   = $DB->get_record('course', ['shortname' => $shortname], 'id', MUST_EXIST);
        $context  = context_course::instance($course->id);
        $instance = $DB->get_record(
            'block_instances',
            ['blockname' => 'coursectrldates', 'parentcontextid' => $context->id],
            '*',
            MUST_EXIST
        );

        // phpcs:disable moodle.PHP.ForbiddenFunctions.Found -- Safe: Moodle-internal block config.
        $config = !empty($instance->configdata)
            ? unserialize(
                base64_decode($instance->configdata),
                ['allowed_classes' => [stdClass::class]]
            )
            : null;
        // phpcs:enable moodle.PHP.ForbiddenFunctions.Found

        if (!is_object($config)) {
            $config = new stdClass();
        }
        $config->show_help = '0';
        $DB->set_field(
            'block_instances',
            'configdata',
            base64_encode(serialize($config)),
            ['id' => $instance->id]
        );
    }

    // Assertions: event list.

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

    // Assertions: splash.

    /**
     * Assert that the Termin-Assistent splash card is present and visible.
     *
     * @Then the setup help notification should be visible
     * @return void
     */
    public function the_setup_help_notification_should_be_visible(): void {
        $selector = '[data-region="coursectrldates-splash"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                'Termin-Assistent splash card not found on page.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert that the Termin-Assistent splash card is NOT present or not visible.
     *
     * @Then the setup help notification should not be visible
     * @return void
     */
    public function the_setup_help_notification_should_not_be_visible(): void {
        $selector = '[data-region="coursectrldates-splash"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if ($node && $node->isVisible()) {
            throw new ExpectationException(
                'Termin-Assistent splash card is visible but should not be.',
                $this->getSession()
            );
        }
    }

    // Assertions: main content visibility.

    /**
     * Assert that the main block content region is hidden by CSS class.
     *
     * This is the expected state while the Termin-Assistent splash is active.
     *
     * @Then the main block content should be hidden
     * @return void
     */
    public function the_main_block_content_should_be_hidden(): void {
        $node = $this->getSession()->getPage()->find(
            'css',
            '[data-region="coursectrldates-main"]'
        );
        if (!$node) {
            throw new ExpectationException(
                'Main block content region (coursectrldates-main) not found on page.',
                $this->getSession()
            );
        }
        if (!$node->hasClass('block-coursectrldates-hidden')) {
            throw new ExpectationException(
                'Main block content is visible but should be hidden.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert that the main block content region is NOT hidden.
     *
     * Expected after deferring (Nein) or after the splash is gone.
     *
     * @Then the main block content should be visible
     * @return void
     */
    public function the_main_block_content_should_be_visible(): void {
        $node = $this->getSession()->getPage()->find(
            'css',
            '[data-region="coursectrldates-main"]'
        );
        if (!$node) {
            throw new ExpectationException(
                'Main block content region (coursectrldates-main) not found on page.',
                $this->getSession()
            );
        }
        if ($node->hasClass('block-coursectrldates-hidden')) {
            throw new ExpectationException(
                'Main block content is hidden but should be visible.',
                $this->getSession()
            );
        }
    }

    // Assertions: block config state.

    /**
     * Assert that show_help is disabled ('0') in the block instance configdata.
     *
     * Reads the serialised PHP directly from the database without going through
     * the block config form, so this works even after a JS-triggered update.
     *
     * @Then the Termin-Assistent should be disabled in the block config for :shortname
     * @param string $shortname Course shortname.
     * @return void
     */
    public function the_termin_assistent_should_be_disabled_in_the_block_config_for(
        string $shortname
    ): void {
        $raw = $this->get_block_configdata_raw($shortname);
        // Serialised PHP stores show_help = '0' as s:9:"show_help";s:1:"0".
        if (strpos($raw, 's:9:"show_help";s:1:"0";') === false) {
            throw new ExpectationException(
                'show_help is not set to "0" in block config for course "' . $shortname . '".',
                $this->getSession()
            );
        }
    }

    /**
     * Assert that show_help is enabled (not '0') in the block instance configdata.
     *
     * @Then the Termin-Assistent should be enabled in the block config for :shortname
     * @param string $shortname Course shortname.
     * @return void
     */
    public function the_termin_assistent_should_be_enabled_in_the_block_config_for(
        string $shortname
    ): void {
        $raw = $this->get_block_configdata_raw($shortname);
        if (strpos($raw, 's:9:"show_help";s:1:"0";') !== false) {
            throw new ExpectationException(
                'show_help is set to "0" (disabled) in block config for course "' . $shortname . '".',
                $this->getSession()
            );
        }
    }

    // Actions: buttons on the splash.

    /**
     * Click the "Nein" (defer) button on the Termin-Assistent splash.
     *
     * Removes the splash from the DOM and reveals the main block content
     * without a server round-trip or page reload.
     *
     * @When I defer the setup help notification
     * @return void
     */
    public function i_defer_the_setup_help_notification(): void {
        $selector = '[data-action="defer-help"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                '"Nein" (defer) button not found on the Termin-Assistent.',
                $this->getSession()
            );
        }
        $node->click();
        // Wait until splash is removed and main content is revealed (no page reload).
        $this->getSession()->wait(
            3000,
            'document.querySelector(\'[data-region="coursectrldates-splash"]\') === null'
            . ' && document.querySelector(\'[data-region="coursectrldates-main"]\')'
            . '   !== null'
        );
    }

    /**
     * Click "Abschalten" on the Termin-Assistent splash.
     *
     * Sends a disable_help POST request, then reloads the page. After the
     * reload, the Termin-Assistent setting is deactivated in the block config
     * and the splash will not reappear.
     *
     * @When I disable the Termin-Assistent
     * @return void
     */
    public function i_disable_the_termin_assistent(): void {
        $selector = '[data-action="disable-help"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                '"Abschalten" button not found on the Termin-Assistent.',
                $this->getSession()
            );
        }
        $node->click();
        // Wait for the POST to complete and the page to reload.
        // After reload: no splash rendered, main content visible (no hidden class).
        $this->getSession()->wait(
            5000,
            'document.querySelector(\'[data-region="coursectrldates-splash"]\') === null'
            . ' && document.querySelector(\'[data-region="coursectrldates-main"]\')'
            . '   !== null'
            . ' && !document.querySelector(\'[data-region="coursectrldates-main"]\')'
            . '   .classList.contains(\'block-coursectrldates-hidden\')'
        );
    }

    /**
     * Click "Ja" on the Termin-Assistent — dismisses permanently and navigates
     * to the Course Control Hub management page.
     *
     * @When I accept the setup help notification
     * @return void
     */
    public function i_accept_the_setup_help_notification(): void {
        $selector = '[data-action="dismiss-help-and-go"]';
        $node = $this->getSession()->getPage()->find('css', $selector);
        if (!$node) {
            throw new ExpectationException(
                '"Ja" button not found on the Termin-Assistent.',
                $this->getSession()
            );
        }
        $node->click();
        // Wait until the page navigates to the Course Control Hub manage page.
        $this->getSession()->wait(
            5000,
            'window.location.href.indexOf("/local/coursectrl/manage.php") !== -1'
        );
    }

    /**
     * @deprecated Use i_disable_the_termin_assistent() instead.
     *
     * Kept for backward compatibility with existing scenarios. Internally
     * delegates to the renamed step.
     *
     * @When I permanently dismiss the setup help notification
     * @return void
     */
    public function i_permanently_dismiss_the_setup_help_notification(): void {
        $this->i_disable_the_termin_assistent();
    }

    // Private helpers.

    /**
     * Return the raw (base64-decoded, not unserialized) configdata for the
     * coursectrldates block in the given course.
     *
     * @param string $shortname Course shortname.
     * @return string Raw serialised PHP configdata (empty string if not set).
     */
    private function get_block_configdata_raw(string $shortname): string {
        global $DB;

        $course   = $DB->get_record('course', ['shortname' => $shortname], 'id', MUST_EXIST);
        $context  = context_course::instance($course->id);
        $instance = $DB->get_record(
            'block_instances',
            ['blockname' => 'coursectrldates', 'parentcontextid' => $context->id],
            'configdata',
            MUST_EXIST
        );

        return !empty($instance->configdata) ? base64_decode($instance->configdata) : '';
    }
}
