@web @project_page
Feature: As a project owner, I should be able to provide and edit name, description, and credits.

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  | description   | credit   |
      | 1  | project 1 | Catrobat  |               |          |
      | 2  | project 2 | OtherUser | description 2 | credit 2 |

  Scenario: A button for editing project should be visible if I am the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    And the element "#edit-project-button" should be visible

  Scenario: A button for editing project should not exist if I am not the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    But the element "#edit-project-button" should not exist

  Scenario: Editing a project is not possible if not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should not exist
    And the element "#edit-text-navigation" should not exist
    And the element "#edit-text-ui" should not exist

  Scenario: Creating all fields is possible if it's my project
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-navigation" should be visible
    And I should see "Default"
    And the element "#edit-default-button" should be visible
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-ui" should be visible
    And the element "#edit-submit-button" should be visible
    And the element "#edit-submit-button" should be disabled
    When I fill in "edit-name-text" with "This is a new name"
    And I fill in "edit-description-text" with "This is a new description"
    And I fill in "edit-credits-text" with "This is a new credit"
    Then the element "#edit-submit-button" should not be disabled
    When I click "#edit-submit-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-ui" should not be visible
    And the element "#edit-text-navigation" should not be visible
    And I should see "This is a new name"
    And I should see "This is a new description"
    And I should see "This is a new credit"
  
  Scenario: Updating all fields is possible if it's my project
    Given I log in as "OtherUser"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#edit-project-button" should be visible
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-navigation" should be visible
    And I should see "Default"
    When I click "#edit-default-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-ui" should be visible
    And the element "#edit-submit-button" should be visible
    And the element "#edit-submit-button" should be disabled
    When I fill in "edit-name-text" with "This is a new name"
    And I fill in "edit-description-text" with "This is a new description"
    And I fill in "edit-credits-text" with "This is a new credit"
    Then the element "#edit-submit-button" should not be disabled
    When I click "#edit-submit-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-ui" should not be visible
    And the element "#edit-text-navigation" should not be visible
    And I should see "This is a new name"
    And I should see "This is a new description"
    And I should see "This is a new credit"
