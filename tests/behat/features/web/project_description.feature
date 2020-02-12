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

  Scenario: Changing a project description is not possible if not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should not exist
    And the element "#edit-description-ui" should not exist

  Scenario: Changing description is not possible if it's not my project
    Given I log in as "OtherUser"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should not exist
    And the element "#edit-description-ui" should not exist

  Scenario: Changing description is possible if it's my project
    Given I log in as "OtherUser"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should be visible
    When I click "#edit-description-button"
    And I wait for AJAX to finish
    Then the element "#description" should not be visible
    But the element "#edit-description" should be visible
    And the element "#edit-description-submit-button" should be visible
    When I fill in "edit-description" with "This is a new description"
    And I click "#edit-description-submit-button"
    And I wait for AJAX to finish
    Then the element "#description" should be visible
    And the element "#edit-description-ui" should not be visible
    And I should see "This is a new description"

  Scenario: Large Project Descriptions are only fully visible when show more was clicked
    Given there are programs with a large description:
      | id | name                     | owned by |
      | 3  | long description project | Catrobat |
    When I go to "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "long description project"
    And I should not see "the end of the description"
    And I should see "Show more"
    When I click "#descriptionShowMoreToggle"
    And I wait for AJAX to finish
    Then I should see "Show Less"
    And I should see "the end of the description"

  Scenario: Small Project Descriptions are fully visible
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "my description"
    And I should not see "Show more"

