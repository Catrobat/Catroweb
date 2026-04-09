@web @project_page
Feature: Projects should have credits where a custom translation can be defined

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | credit     |
      | 1  | project 1 | Catrobat | my credits |
      | 2  | project 2 | Catrobat |            |

  Scenario: Credit text field should be disabled if there is not a default credit defined
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "project 2"
    When I click "#edit-project-button"
    And I wait for the element "#edit-default-button" to be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then the element "#edit-credits-text" should be disabled
    And I should see "No notes and credits available."
