@web @studio
Feature: Every Studio should have an overview containing the most necessary information

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
    And there are programs:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Catrobat    | 1         | member |
    And there are studio projects:
      | id | project   | user     | studio_id |
      | 1  | program 1 | Catrobat | 1         |
    And there are studio comments:
      | id | comment     | user     | studio_id |
      | 1  | Cool studio | Catrobat | 1         |

  Scenario: Besides the overview every studio has a project and a comments tab
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I should see "CatrobatStudio01"
    And I should see "hasADescription"
    And I should see "public"
    And the ".member_count" element should contain "2"
    And the ".activity_count" element should contain "4"
    Then the ".mdc-tab-bar" element should contain "projects"
    And the ".mdc-tab-bar" element should contain "comments"
    And the element "#projects-pane" should be visible
    And the element "#comments-pane" should not be visible
    When I click "#comments-tab"
    Then the element "#projects-pane" should not be visible
    And the element "#comments-pane" should be visible
    When I click "#projects-tab"
    Then the element "#comments-pane" should not be visible
    And the element "#projects-pane" should be visible



