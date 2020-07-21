@web @project_page
Feature: As a user I want to be able to steal projects from other users

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | downloads | owned by  | apk_ready |
      | 1  | project 1 | 5         | Catrobat  | true      |
      | 2  | project 2 | 5         | OtherUser | true      |

  Scenario: The button should not be visible when I am not logged in
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#steal-project-button" should not exists

  Scenario: The button should not be visible when I am logged in and I am the owner of this project
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#steal-project-button" should not exists

  Scenario: The button should be visible when I am logged in and I am not the owner of this project
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#steal-project-button" should be visible

  Scenario: When clicking the button I am the owner of this project
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#steal-project-button" should be visible
    When I click "#steal-project-button"
    Then "project 2" should be owned by "Catrobat"