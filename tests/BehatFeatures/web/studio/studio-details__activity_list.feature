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
      | id                                   | user        | studio_id | role   |
      | 00000000-0000-0000-0000-000000000001 | StudioAdmin | 1         | admin  |
      | 00000000-0000-0000-0000-000000000002 | Catrobat    | 1         | member |
    And there are studio projects:
      | project   | user     | studio_id |
      | program 1 | Catrobat | 1         |
    And there are studio comments:
      | comment     | user     | studio_id |
      | Cool studio | Catrobat | 1         |

  Scenario: If I am not logged in I should not be able see all members
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".studio-detail__header__details__button--activity" should not exist
    And the ".activity_count" element should contain "4"

  Scenario: If I am only a member i can't see the activity
    Given I log in as "Catrobat"
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".studio-detail__header__details__button--activity" should not exist
    And the ".activity_count" element should contain "4"

  Scenario: If I am an admin I must have read access to the members list
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
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