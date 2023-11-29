@web @project_page
Feature: A user should be able to steal programs that belongs to another user

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Fabian   |
    And there are projects:
      | id | name      | description            | owned by | apk_ready |
      | 1  | project 1 | Catrobat's description | Catrobat | true      |
      | 2  | project 2 | Fabian's description   | Fabian   | true      |

  Scenario: Steal project
    Given I log in as "Fabian"
    And I go to "/app/project/1"
    And one of the "a" elements should contain "Catrobat"
    When I click on the button named "stealProgramButton"
    Then I wait for the page to be loaded
    And I should be on "/app/project/1"
    And one of the "a" elements should contain "Fabian"
    And I wait for the element "#stealProgramButton" to be not visible


  Scenario: It is not possible to see the steal button if the project belongs to the user
    Given I log in as "Fabian"
    And I go to "/app/project/2/"
    Then I wait for the element "#stealProgramButton" to be not visible

  Scenario: It is not possible to see the steal button if there is no user logged in
    Given I go to "/app/project/1/"
    And I wait for the page to be loaded
    Then I wait for the element "#stealProgramButton" to be not visible
