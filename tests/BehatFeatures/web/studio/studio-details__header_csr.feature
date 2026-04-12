@web @studio
Feature: Studio header renders content from API (CSR)

  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |
      | 2  | Catrobat    |
      | 3  | Guest       |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
      | 2  | PrivateStudio02  | privateDesc     | true           | false     |
    And there are studio users:
      | id                                   | user        | studio_id | role   |
      | 00000000-0000-0000-0000-000000000001 | StudioAdmin | 1         | admin  |
      | 00000000-0000-0000-0000-000000000002 | Catrobat    | 1         | member |
      | 00000000-0000-0000-0000-000000000003 | StudioAdmin | 2         | admin  |

  Scenario: Studio header displays name and description after API load
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "CatrobatStudio01"
    And I should see "hasADescription"

  Scenario: Visibility badge shows public for public studio
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#header-visibility" to contain "public"

  Scenario: Visibility badge shows private for private studio
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/2"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#header-visibility" to contain "Invite-only"

  Scenario: Member count renders from API data
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the ".member_count" element should contain "2"

  Scenario: Activity count renders from API data
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".activity_count" should be visible

  Scenario: Admin sees edit studio button in options menu
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should be visible

  Scenario: Non-admin member cannot see edit studio button in options menu
    Given I log in as "Catrobat"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should not be visible

  Scenario: Non-member cannot see edit studio button in options menu
    Given I log in as "Guest"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should not be visible

  Scenario: Anonymous user cannot see edit studio button in options menu
    Given I am on "/app/studio/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    Then the element "#top-app-bar__btn-edit-studio" should not be visible
