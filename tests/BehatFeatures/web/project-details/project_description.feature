@web @project_page
Feature: Projects should have descriptions that can be changed by the project owner

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  | description    |
      | 1  | project 1 | Catrobat  | my description |
      | 2  | project 2 | OtherUser |                |

  Scenario: Changing description is possible if it's my project
    Given I log in as "OtherUser"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be visible
    When I fill in "edit-description-text" with "This is a new description"
    Then the element "#edit-submit-button" should not be disabled
    When I click "#edit-submit-button"
    And I wait for AJAX to finish
    Then the element "#description" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "This is a new description"
  
  Scenario: Editing description, closing the editor while saving edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a new description"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "#description" should be visible
    And the element "#edit-text-navigation" should not be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "This is a new description"

  Scenario: Editing description, closing the editor while discarding edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a new description"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-deny"
    Then the element "#edit-text-navigation" should be visible
    When I click "#top-app-bar__back__btn-back"
    Then the element "#description" should be visible
    And the element "#edit-text-navigation" should not be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "my description"

  Scenario: Editing description, closing the editor but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a new description"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-close"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be visible
    Then the "edit-description-text" field should contain "This is a new description"

  Scenario: Large Project Descriptions are only fully visible when show more was clicked
    Given there are projects with a large description:
      | id | name                     | owned by |
      | 3  | long description project | Catrobat |
    When I go to "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "long description project"
    And I should see "MORE INFORMATION"
    When I click "#descriptionShowMoreToggle"
    And I wait for AJAX to finish
    Then I should see "LESS INFORMATION"
    And I should see "the end of the description"

  Scenario: Small Project Descriptions are fully visible
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "my description"
    And I should not see "Show more"

