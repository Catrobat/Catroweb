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
    And the search index is up-to-date

  Scenario: search for projects with full name should work
    When I am on "/app/search/User3"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#search-projects" to contain "project 3"
    Then I should see "Results for"
    And I should not see "project 1"
    And I should not see "project 2"
    But I should see "project 3"

  Scenario: search for projects with parts of name should work
    When I am on "/app/search/User"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#search-projects" to contain "project 3"
    Then I should see "Results for"
    And I should not see "project 1"
    And I should see "project 2"
    But I should see "project 3"
