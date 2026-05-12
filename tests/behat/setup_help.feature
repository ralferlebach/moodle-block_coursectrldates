@block @block_coursectrldates @block_coursectrldates_setup_help
Feature: Course dates block Termin-Assistent notification
  As an editing teacher
  I want to be notified when a course is newly set up so I can adjust the dates
  And I want to control whether this notification reappears

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

  @javascript
  Scenario: Termin-Assistent appears when the course was recently created
    Given I log in as "teacher1"
    When I am on "C1" course homepage
    Then the setup help notification should be visible

  @javascript
  Scenario: Clicking Nein hides the Termin-Assistent for this page load only
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I defer the setup help notification
    Then the setup help notification should not be visible
    And the main block content should be visible
    # After reload, the notification reappears because nothing was persisted.
    When I am on "C1" course homepage
    Then the setup help notification should be visible

  @javascript
  Scenario: Clicking Abschalten disables the Termin-Assistent permanently
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I disable the Termin-Assistent
    Then the setup help notification should not be visible
    And the main block content should be visible
    # After reload, the notification stays gone — preference and config were saved.
    When I am on "C1" course homepage
    Then the setup help notification should not be visible

  @javascript
  Scenario: Clicking Ja dismisses the Termin-Assistent and navigates to course management
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I accept the setup help notification
    Then the URL should contain "/local/coursectrl/manage.php"
    # After returning to the course page the notification must stay gone.
    When I am on "C1" course homepage
    Then the setup help notification should not be visible
