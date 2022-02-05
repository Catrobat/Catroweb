@web @project_page
Feature: Projects should have a name that can be changed by the project owner

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  |
      | 1  | project 1 | Catrobat  |
      | 2  | project 2 | OtherUser |

  Scenario: Changing a project name is not possible if not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-name-button" should not exist
    And the element "#edit-text-ui" should not exist

  Scenario: Changing name is not possible if it's not my project
    Given I log in as "OtherUser"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-name-button" should not exist
    And the element "#edit-text-ui" should not exist

  Scenario: Changing name is possible if it's my project
    Given I log in as "OtherUser"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#edit-name-button" should be visible
    When I click "#edit-name-button"
    And I wait for AJAX to finish
    Then the element "#edit-text" should be visible
    And the element "#edit-submit-button" should be visible
    When I fill in "edit-text" with "This is a new name"
    And I click "#edit-submit-button"
    And I wait for AJAX to finish
    Then the element "#name" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "This is a new name"
  
  Scenario: Editing name, closing the editor while saving edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-name-button" should be visible
    When I click "#edit-name-button"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a new name"
    And I click "#edit-close-button"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "#name" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "This is a new name"

  Scenario: Editing name, closing the editor while discarding edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-name-button" should be visible
    When I click "#edit-name-button"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a new name"
    And I click "#edit-close-button"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-deny"
    Then the element "#name" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "project 1"

  Scenario: Editing name, closing the editor but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-name-button" should be visible
    When I click "#edit-name-button"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a new name"
    And I click "#edit-close-button"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-close"
    Then the element "#edit-text" should be visible
    Then the "edit-text" field should contain "This is a new name"
