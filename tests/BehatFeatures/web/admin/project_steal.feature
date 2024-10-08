@web @project_page
Feature: As a logged-in user, I want to be able to steal a project

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |

  Scenario: I want to steal a project via the steal button
    Given I log in as "User"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "button[id^='projectStealButton']" should be visible
    And I click "button[id^='projectStealButton']"
    And I wait for the element "#feedback-snackbar" to be visible
    Then the element "#feedback-snackbar" should contain "You successfully stole the project"
