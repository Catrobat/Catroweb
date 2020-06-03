@web @project_page
Feature: As a visitor I want to steal a project

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | user1    |
    And there are projects:
      | id | name      | downloads | owned by | views | apk_ready | upload time      |
      | 1  | project 1 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 |
      | 2  | project 2 | 10        | user1    | 42    | false     | 01.01.2014 12:00 |

  Scenario: I want to steal a project via the button
    Given I log in as "Catrobat"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#steal-project" should be visible
    When I click "#steal-project"
    Then I wait for AJAX to finish
    And I wait for the page to be loaded
    Then the "#icon-author .icon-text" element should contain "Catrobat"
    And the element "#steal-project" should not exist


  Scenario: I don't want to steal my own projects
    Given I log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#steal-project" should not exist
