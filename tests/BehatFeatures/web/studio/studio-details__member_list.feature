@web @studio
Feature: Every studio provides a list of all members

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Catrobat    | 1         | member |
    And there are studio projects:
      | id | project   | user     | studio_id |
      | 1  | project 1 | Catrobat | 1         |

  Scenario: If I am not logged in I should not be able see all members
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--member" should not exist
    And the ".member_count" element should contain "2"

  Scenario: If I am a member or admin I must have read access to the members list
    Given I log in as "Catrobat"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--member" should be visible
    And the ".member_count" element should contain "2"
    When I click ".studio-detail__header__details__button--member"
    And I wait for the page to be loaded
    Then I should see "Studio members"
    Then I should see 2 ".member__list-entry"
    Then I should see 2 ".member__list-entry__image"
    And I should see "StudioAdmin"
    And I should see "No studio projects"
    And I should see "Catrobat"
    And I should see "1 studio project"
    And I should see 1 ".member__list-entry__admin-indicator"
    And the element ".member__list-entry__admin-buttons" should not exist

  Scenario: If I am the admin of the studio, I must have read access to the members list
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--member" should be visible
    And the ".member_count" element should contain "2"
    When I click ".studio-detail__header__details__button--member"
    And I wait for the page to be loaded
    Then I should see "Studio members"
    Then I should see 2 ".member__list-entry"
    Then I should see 2 ".member__list-entry__image"
    And I should see "StudioAdmin"
    And I should see "No studio projects"
    And I should see "Catrobat"
    And I should see "1 studio project"
    And I should see 1 ".member__list-entry__admin-indicator"
    And the element ".member__list-entry__admin-buttons" should exist

  Scenario: If I am the admin of the studio, I can promote members
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--member" should be visible
    And the ".member_count" element should contain "2"
    When I click ".studio-detail__header__details__button--member"
    And I wait for the page to be loaded
    And I should see 1 ".member__list-entry__admin-indicator"
    When I click ".member__list-entry__admin-button"
    And I wait for the page to be loaded
    When I click ".member__list-entry__admin-button__promote"
    And I wait for the page to be loaded
    And I should see 2 ".member__list-entry__admin-indicator"

  Scenario: If I am the admin of the studio, I can ban members
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    Then the element ".studio-detail__header__details__button--member" should be visible
    And the ".member_count" element should contain "2"
    When I click ".studio-detail__header__details__button--member"
    And I wait for the page to be loaded
    Then I should see 2 ".member__list-entry"
    And I should see 1 ".member__list-entry__admin-indicator"
    When I click ".member__list-entry__admin-button"
    And I wait for the page to be loaded
    When I click ".member__list-entry__admin-button__ban"
    And I wait for the page to be loaded
    Then I should see 1 ".member__list-entry"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And the ".member_count" element should contain "1"