@web
Feature: I can steal a project from an other user

  Background:
    Given there are users:
      | id | name     |
      | 1  | Andi     |
      | 2  | Klaus    |
    And there are projects:
      | id | name      | description             | owned by |
      | 1  | project 1 | Klaus ist brave         | Klaus    |
      | 2  | project 2 | Klaus hat zwei project  | Klaus    |

  Scenario: I want to steal the program form Klaus
    Given I am logged in as "Andi"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    And I should see "Klaus"
    And I click "#steal-button"
    And I wait for AJAX to finish
    Then I should be on "/app/project/1"
    And I wait for the page to be loaded
    And I should see "Andi"






