@web @studio
Feature: Studio project cards display metadata and action menus

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
      | 3  | Visitor     |
    And there are projects:
      | id | name      | description    | owned by    |
      | 1  | project 1 | my description | StudioAdmin |
      | 2  | project 2 | my description | Catrobat    |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Catrobat    | 1         | member |
    And there are studio projects:
      | id | studio_id | project   | user        |
      | 1  | 1         | project 1 | StudioAdmin |
      | 2  | 1         | project 2 | Catrobat    |

  Scenario: Project cards render with project names after API load
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"
    Then the element ".projects-list-item-wrapper[data-project-id='1']" should be visible
    And the element ".projects-list-item-wrapper[data-project-id='2']" should be visible

  Scenario: Admin sees Open, Download, Share, and Remove in project card menu
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"
    When I click ".projects-list-item-wrapper[data-project-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    Then I should see "Open project"
    And I should see "Download"
    And I should see "Share"
    And I should see "Remove from studio"

  Scenario: Member sees Remove option for projects in studio
    Given I log in as "Catrobat"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 2"
    When I click ".projects-list-item-wrapper[data-project-id='2'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    Then I should see "Remove from studio"

  Scenario: Remove project confirmation shows specific dialog text
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"
    When I click ".projects-list-item-wrapper[data-project-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    And I click "[data-menu-action='remove']"
    And I wait 500 milliseconds
    Then I should see "Remove project from studio?"
    And I should see "You can add it again later if you change your mind."

  Scenario: Cancel remove project keeps the project in the list
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"
    When I click ".projects-list-item-wrapper[data-project-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    And I click "[data-menu-action='remove']"
    And I wait 500 milliseconds
    And I click ".swal2-cancel"
    And I wait 500 milliseconds
    Then I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"

  Scenario: Anonymous user sees Open but not Remove in project card menu
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"
    When I click ".projects-list-item-wrapper[data-project-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    Then I should see "Open project"
    And I should not see "Remove from studio"

  Scenario: Non-member user sees Open but not Remove in project card menu
    Given I log in as "Visitor"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "[data-studio--project-list-target='container']" to contain "project 1"
    When I click ".projects-list-item-wrapper[data-project-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    Then I should see "Open project"
    And I should not see "Remove from studio"
