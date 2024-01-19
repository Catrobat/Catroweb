@web @studio
Feature: Every studio provides a list of all members

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
      | 2  | program 2 | Catrobat |
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

  Scenario: If I am not logged in I should not be able see all members
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--activity" should not exist
    And the ".activity_count" element should contain "4"

  Scenario: If I am only a member i can't see the activity
    Given I log in as "Catrobat"
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--activity" should not exist
    And the ".activity_count" element should contain "4"

  Scenario: If I am an admin I must have read access to the members list
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--activity" should be visible
    And the ".activity_count" element should contain "4"
    When I click ".studio-detail__header__details__button--activity"
    And I wait for the page to be loaded
    Then I should see "Activities"
    Then I should see 4 ".activity__list-entry"
    And I should see "StudioAdmin joined the studio"
    And I should see "Catrobat joined the studio"
    And I should see "Catrobat added a project"
    And I should see "Catrobat left a comment"