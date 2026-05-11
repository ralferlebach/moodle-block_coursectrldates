@block @block_coursectrldates @block_coursectrldates_setup_help
Feature: Course dates block setup help notification
  As an editing teacher
  I want to be notified when a course is newly set up so I can check the dates
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
  Scenario: Setup help notification appears when course was recently created
    Given I log in as "teacher1"
    When I am on "C1" course homepage
    Then the setup help notification should be visible

  @javascript
  Scenario: Deferring the notification hides it for this page load only
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I defer the setup help notification
    Then the setup help notification should not be visible
    # After reload the notification reappears because nothing was persisted.
    When I am on "C1" course homepage
    Then the setup help notification should be visible

  @javascript
  Scenario: Permanently dismissing the notification persists after reload
    Given I log in as "teacher1"
    And I am on "C1" course homepage
    And the setup help notification should be visible
    When I permanently dismiss the setup help notification
    Then the setup help notification should not be visible
    # After reload it must stay gone — preference was saved server-side.
    When I am on "C1" course homepage
    Then the setup help notification should not be visible
