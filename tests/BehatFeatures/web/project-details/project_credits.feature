@web @project_page
Feature: As a project owner, I should be able to give credits for my project.

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  |
      | 1  | project 1 | Catrobat  |
      | 2  | project 2 | OtherUser |

  Scenario: There should be a credits section on every project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Notes and credits"
    But the element ".edit-credits-button" should not exist

  Scenario: A button for editing credits should be visible if I am the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Notes and credits"
    And the element ".edit-credits-button" should be visible

  Scenario: A button for editing credits should not exist if I am not the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "Notes and credits"
    But the element ".edit-credits-button" should not exist

  Scenario: I should be able to write new credits, if I am the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Notes and credits"
    And the element ".edit-credits-button" should be visible
    When I click ".edit-credits-button"
    And I wait for AJAX to finish
    Then the element "#edit-text" should be visible
    And I write "This is a credit" in textarea
    When I click "#edit-submit-button"
    And I wait for AJAX to finish
    Then I should see "This is a credit"
  
  Scenario: Editing credits, closing the editor while saving edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-credits-button" should be visible
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "These are new notes and credits"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "#credits" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "These are new notes and credits"

  Scenario: Editing credits, closing the editor while discarding edits
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-credits-button" should be visible
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "These are new notes and credits"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-deny"
    Then the element "#credits" should be visible
    And the element "#edit-text-ui" should not be visible
    And I should see "No notes and credits"

  Scenario: Editing credits, closing the editor but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-credits-button" should be visible
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "These are new notes and credits"
    And I click "#top-app-bar__back__btn-back"
    And I should see "Do you want to save your changes?"
    When I click ".swal2-close"
    And I wait for AJAX to finish
    Then the element "#edit-text" should be visible
    Then the "edit-text" field should contain "These are new notes and credits"
