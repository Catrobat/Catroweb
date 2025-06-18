@web @search
Feature: Searching for projects with ownername

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
      | 3  | User3    |

    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | User2    |
      | 3  | project 3 | User3    |
    And I wait 500 milliseconds

  Scenario: search for projects with full name should work
    When I am on "/app/search/User3"
    And I wait for the page to be loaded
    Then I should see "Search results"
    And I should not see "project 1"
    And I should not see "project 2"
    But I should see "project 3"

  Scenario: search for projects with parts of name should work
    When I am on "/app/search/User"
    And I wait for the page to be loaded
    Then I should see "Search results"
    And I should not see "project 1"
    And I should see "project 2"
    But I should see "project 3"
