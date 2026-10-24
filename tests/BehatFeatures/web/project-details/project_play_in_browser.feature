@web @project_page
Feature: As a visitor I want to play projects in the browser

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name        | owned by | language version |
      | 1  | new project | Catrobat | 1.12             |
      | 2  | old project | Catrobat | 0.925            |

  Scenario: A project the browser player supports links to it
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "new project"
    Then the element ".js-project" should have a attribute "data-player-url" with value "/#1"

  Scenario: A project older than the browser player supports does not
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "old project"
    Then the element ".js-project" should have no attribute "data-player-url" with value "/#"
    And the element "#projectPlayButton" should not exist
