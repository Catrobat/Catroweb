@web @project_browse
Feature: Projects browse page shows user's projects and explore sections

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | User2    |

  Scenario: Logged-in user sees My Projects and Explore Projects sections
    Given I log in as "Catrobat"
    And I am on "/app/projects"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".projects-section" should be visible
    And I should see "project 1"

  Scenario: Not-logged-in user sees only Explore Projects
    Given I am on "/app/projects"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".projects-section" should be visible
    And I should see "project 2"

  Scenario: Upload FAB button is visible
    Given I am on "/app/projects"
    And I wait for the page to be loaded
    Then the element ".projects-fab" should be visible

  Scenario: Upload FAB redirects logged-in user to upload page
    Given I log in as "Catrobat"
    And I am on "/app/projects"
    And I wait for the page to be loaded
    When I click ".projects-fab"
    And I wait for the page to be loaded
    Then I should be on "/app/project/upload"

  Scenario: Upload FAB redirects not-logged-in user to login
    Given I am on "/app/projects"
    And I wait for the page to be loaded
    When I click ".projects-fab"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
