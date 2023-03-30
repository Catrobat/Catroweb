@web @project_page
Feature: As user i want to claim projects from other users

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | Tester    |
    And there are projects:
      | id | name     | downloads | owned by  | private | apk_ready |
      | 1  | project 1 | 5        | Catrobat  | false   | true      |
      | 2  | project 2 | 5        | Tester    | false   | true      |
      | 3  | project 3 | 5        | Tester    | false   | true      |

  Scenario: I want to not see the claim a project as a not logged in user
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectClaimProjectButton" should not exist

  Scenario: I want to see the claim a project as a logged in user on another users project
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#projectClaimProjectButton" should exist

  Scenario: I want to not see the claim a project as a logged in user on my own project
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectClaimProjectButton" should not exist

  Scenario: I want to be the owner of a project after i click the claim Project button
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    When I click "#projectClaimProjectButton"
    And I wait for AJAX to finish
    And I wait 150 milliseconds
    And the element "#share-snackbar" should be visible
    And I should see "You successfully claimed this project!"
    When I reload the page
    And I wait for the page to be loaded
    Then the element "#projectClaimProjectButton" should not exist
    And I should see "Catrobat"
    And I should not see "Tester"
