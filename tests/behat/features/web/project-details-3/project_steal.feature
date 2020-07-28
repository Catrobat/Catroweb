@web @project_page
Feature: As a user I want to be able to steal projects from other users

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      |  owned by  |
      | 1  | project 1 |  Catrobat  |
      | 2  | project 2 |  OtherUser |

  Scenario: The button should not be visible when I am not logged in
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    But I should not see "#steal-button"


  Scenario: The button should not be visible when I am logged in and I am the owner of this project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    But I should not see "#steal-button"


  Scenario: When clicking the button I am the owner of this project
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    When I click "#steal-button"
    And I wait for AJAX to finish
    And I wait for the page to be loaded
    And I should not see "#steal-button"