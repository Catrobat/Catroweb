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
    Then I should see "Credits"
    But the element "#edit-credits-button" should not exist

  Scenario: A button for editing credits should be visible if I am the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Credits"
    And the element "#edit-credits-button" should be visible

  Scenario: A button for editing credits should not exist if I am not the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "Credits"
    But the element "#edit-credits-button" should not exist

  Scenario: If I click the edit credits button, a textarea should appear in which I can write my credits
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Credits"
    And the element "#edit-credits-button" should be visible
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then the element "#edit-credits" should be visible

  Scenario: I should be able to write new credits, if I am the owner of the project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Credits"
    And the element "#edit-credits-button" should be visible
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then the element "#edit-credits" should be visible
    And I write "This is a credit" in textarea
    When I click "#edit-credits-submit-button"
    And I wait for AJAX to finish
    Then I should see "This is a credit"