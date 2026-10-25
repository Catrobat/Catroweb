@web @project_page
Feature: As a visitor I want to play projects in the browser

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Visitor  |
    And there are projects:
      | id | name            | owned by | version | language version | private |
      | 1  | new project     | Catrobat | 1.4.1   | 1.12             | false   |
      | 2  | old project     | Catrobat | 0.7.3   | 0.7              | false   |
      | 3  | private project | Catrobat | 1.4.1   | 1.12             | true    |
      | 4  | legacy project  | Catrobat | 0.9.23  | 0.925            | false   |

  Scenario: A project the browser player supports links to it
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "new project"
    Then the element ".js-project" should have a attribute "data-player-url" with value "/#1"

  Scenario: A project from before scenes links to the player too, which converts it as the app does
    Given I am on "/app/project/4"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "legacy project"
    Then the element ".js-project" should have a attribute "data-player-url" with value "/#4"

  Scenario: A project older than the browser player supports has no player link
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "old project"
    Then the element ".js-project" should have no attribute "data-player-url" with value "/#"
    And the element "#projectPlayButton" should not exist

  Scenario: A private project has no player link, as the player only loads public projects
    Given I log in as "Catrobat"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#name" to contain "private project"
    Then the element ".js-project" should have no attribute "data-player-url" with value "/#"

  Scenario: The details show which app version made the project
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#details-version" should contain "Made with app version 0.7.3 (Catrobat 0.7)"

  Scenario: The owner of a project too old for the browser player is told how to update it
    Given I log in as "Catrobat"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#old-version-hint" should be visible
    And the element "#old-version-hint" should contain "Open it in Pocket Code and upload it again"

  Scenario: Other visitors of an old project see no update hint
    Given I log in as "Visitor"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#old-version-hint" should not exist

  Scenario: The owner of a project the browser player supports sees no update hint
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#old-version-hint" should not exist
