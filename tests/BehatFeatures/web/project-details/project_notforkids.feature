@web @project_page
Feature: As a project owner I want to be able to toggle not-for-kids via the options menu

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | downloads | owned by | not_for_kids |
      | 1  | project 1 | 5         | Catrobat | 0            |
      | 2  | project 2 | 5         | User     | 1            |
      | 3  | project 3 | 5         | User     | 2            |
      | 4  | project 4 | 5         | User     | 0            |

  Scenario: Non-owner does not see the not-for-kids toggle in options menu
    Given I log in as "Catrobat"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-toggle-not-for-kids" should not exist

  Scenario: Owner sees the not-for-kids toggle in options menu
    Given I log in as "User"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-toggle-not-for-kids" should exist

  Scenario: Owner sees the visibility toggle in options menu
    Given I log in as "User"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-toggle-visibility" should exist