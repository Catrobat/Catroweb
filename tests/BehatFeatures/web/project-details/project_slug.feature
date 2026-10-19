@web @project_page
Feature: Projects can be opened by their custom URL slug

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  | slug         |
      | 1  | project 1 | Catrobat  | my-cool-game |
      | 2  | project 2 | OtherUser |              |

  Scenario: The slug URL opens the project
    Given I go to "/app/project/my-cool-game"
    And I wait for the page to be loaded
    Then I should be on "/app/project/my-cool-game"
    And I wait for the element "#name" to contain "project 1"

  Scenario: The slug is matched case-insensitively
    Given I go to "/app/project/My-Cool-Game"
    And I wait for the page to be loaded
    Then I wait for the element "#name" to contain "project 1"

  Scenario: The id URL keeps working for a project with a slug
    Given I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"
    And I wait for the element "#name" to contain "project 1"

  Scenario: An unknown slug redirects to the start page
    Given I go to "/app/project/no-such-game"
    And I wait for the page to be loaded
    Then I should be on "/app/"

  Scenario: The owner gets the custom URL menu item, others do not
    Given I log in as "OtherUser"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-custom-url" should exist
    When I go to "/app/project/my-cool-game"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-custom-url" should not exist
