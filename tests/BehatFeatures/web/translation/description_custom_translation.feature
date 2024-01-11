@web @project_page
Feature: Projects should have descriptions where a custom translation can be defined

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | description    |
      | 1  | project 1 | Catrobat | my description |
      | 2  | project 2 | Catrobat |                |
    And I wait 1000 milliseconds

  Scenario: Description text field should be disabled if there is not a default description defined
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    And I wait 10000 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be disabled
    And I should see "No description available."
