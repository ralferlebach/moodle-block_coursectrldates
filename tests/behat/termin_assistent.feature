@block @block_coursectrldates @block_coursectrldates_termin_assistent
Feature: Termin-Assistent splash behaviour and configuration
  As an editing teacher
  I want the Termin-Assistent to be the only visible content while it is active
  And clicking its buttons should behave consistently across page loads

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the course dates block is added to the "C1" course

  # ── Splash visibility ─────────────────────────────────────────────────

  @javascript
  Scenario: While Termin-Assistent is active the calendar and event list are hidden
    Given I log in as "teacher1"
    When I am on "C1" course homepage
    Then the setup help notification should be visible
    And the main block content should be hidden

  @javascript
  Scenario: Clicking Nein reveals the calendar and event list without a page reload
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    And the main block content should be hidden
    When I defer the setup help notification
    Then the setup help notification should not be visible
    And the main block content should be visible

  @javascript
  Scenario: After Nein the main content stays visible until the next full page load
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    When I defer the setup help notification
    Then the main block content should be visible
    # Reload: trigger still fires so splash returns, main content hidden again.
    When I am on "C1" course homepage
    Then the setup help notification should be visible
    And the main block content should be hidden

  # ── Abschalten ────────────────────────────────────────────────────────

  @javascript
  Scenario: Clicking Abschalten shows the normal block content after a reload
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I disable the Termin-Assistent
    Then the setup help notification should not be visible
    And the main block content should be visible

  @javascript
  Scenario: Abschalten deactivates show_help in the block config
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I disable the Termin-Assistent
    Then the Termin-Assistent should be disabled in the block config for "C1"

  @javascript
  Scenario: After Abschalten the Termin-Assistent does not reappear on subsequent visits
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I disable the Termin-Assistent
    Then the setup help notification should not be visible
    # A second and third visit confirm the config change is persistent.
    When I am on "C1" course homepage
    Then the setup help notification should not be visible
    And the main block content should be visible
    When I am on "C1" course homepage
    Then the setup help notification should not be visible

  # ── Deactivated config ────────────────────────────────────────────────

  @javascript
  Scenario: When Termin-Assistent is disabled in config no splash is shown
    Given the Termin-Assistent is disabled in the block config for "C1"
    And I log in as "teacher1"
    When I am on "C1" course homepage
    Then the setup help notification should not be visible
    And the main block content should be visible

  @javascript
  Scenario: A second teacher is not affected by another teacher dismissing Abschalten
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher2 | Teacher   | Two      | teacher2@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher2 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I disable the Termin-Assistent
    Then the Termin-Assistent should be disabled in the block config for "C1"
    And I log out
    # Abschalten affects the config for ALL teachers (show_help=0 is instance-level).
    Given I log in as "teacher2"
    When I am on "C1" course homepage
    Then the setup help notification should not be visible

  # ── Ja / navigation ───────────────────────────────────────────────────

  @javascript
  Scenario: Clicking Ja navigates to the Course Control Hub management page
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I accept the setup help notification
    Then the URL should contain "/local/coursectrl/manage.php"

  @javascript
  Scenario: After clicking Ja the splash does not reappear when returning
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I accept the setup help notification
    # Return to course page — dismissed preference was saved.
    When I am on "C1" course homepage
    Then the setup help notification should not be visible
    And the main block content should be visible
