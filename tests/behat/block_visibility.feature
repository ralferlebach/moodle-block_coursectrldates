@block @block_coursectrldates @block_coursectrldates_visibility
Feature: Course dates block visibility and access control
  As a site administrator
  I want the Course dates block to be visible only to users with the view capability
  So that course date information is shown only to appropriate roles

  Background:
    Given the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the course dates block is added to the "C1" course

  @javascript
  Scenario: Editing teacher sees the event list in the Course dates block
    Given I log in as "teacher1"
    When I am on "C1" course homepage
    Then the course dates event list should be visible

  @javascript
  Scenario: Student without view capability cannot see event list content
    Given I log in as "student1"
    When I am on "C1" course homepage
    Then the course dates event list should not be visible

  @javascript
  Scenario: Block shows no-events message when course has no dated activities
    Given I log in as "teacher1"
    When I am on "C1" course homepage
    Then the course dates event list should be visible
    And I should see "No upcoming course dates"
