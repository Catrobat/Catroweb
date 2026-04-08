@web @project_page
Feature: As a project owner I want to be able to delete my project from the project detail page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | User     |

  Scenario: Non-owner does not see delete button in options menu
    Given I log in as "Catrobat"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-delete-project" should not exist

  Scenario: Guest does not see delete button in options menu
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-delete-project" should not exist

  Scenario: Owner sees delete button in options menu
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-delete-project" should exist
