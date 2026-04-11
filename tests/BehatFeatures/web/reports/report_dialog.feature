@web @reports
Feature: Report dialog uses category buttons for selecting report reason

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User2    | 123456   |
    And there are projects:
      | id | name     | owned by | description   |
      | 1  | project1 | Catrobat | mydescription |

  Scenario: Report dialog shows category buttons for project
    Given I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait 500 milliseconds
    Then the element ".swal2-popup" should be visible
    And the element ".report-dialog" should be visible
    And the element ".report-dialog__option[data-report-category='copyright']" should be visible
    And the element ".report-dialog__option[data-report-category='spam']" should be visible
    And the element ".report-dialog__option[data-report-category='other']" should be visible
    And the element "#report-category-value" should exist

  Scenario: Selecting a report category highlights the option
    Given I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait 500 milliseconds
    And I click ".report-dialog__option[data-report-category='copyright']"
    Then the element ".report-dialog__option[data-report-category='copyright']" should have a attribute "aria-checked" with value "true"
    And the element ".report-dialog__option[data-report-category='spam']" should have a attribute "aria-checked" with value "false"

  Scenario: Report dialog shows note textarea
    Given I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait 500 milliseconds
    Then the element ".report-dialog__note" should be visible

  Scenario: Report dialog shows category buttons for user profile report
    Given I log in as "User2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-user"
    And I wait 500 milliseconds
    Then the element ".swal2-popup" should be visible
    And the element ".report-dialog" should be visible
    And the element ".report-dialog__option" should be visible
    And the element "#report-category-value" should exist
