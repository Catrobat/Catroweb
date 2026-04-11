@web @profile_page
Feature: Profile page shows user studios with role badges and actions

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And there are studios:
      | id | name          | description | allow_comments | is_public |
      | 1  | PublicStudio   | pub desc    | true           | true      |
      | 2  | PrivateStudio  | priv desc   | true           | false     |
    And there are studio users:
      | id | user     | studio_id | role   |
      | 1  | Catrobat | 1         | admin  |
      | 2  | Catrobat | 2         | member |

  Scenario: Studios count tab updates from API data on own profile
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element "#studios-count" should exist

  Scenario: Studios tab shows studio names on own profile
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    Then I should see "PublicStudio"
    And I should see "PrivateStudio"

  Scenario: Studio card shows admin pill for admin role
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    Then the element ".studios-list-item-wrapper[data-studio-id='1'] .studios-list-item--pill-admin" should be visible

  Scenario: Private studio card shows lock badge
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    Then the element ".studios-list-item-wrapper[data-studio-id='2'] .studios-list-item--badge" should be visible

  Scenario: Studio card has Open and Share menu items
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    And I click ".studios-list-item-wrapper[data-studio-id='1'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    Then I should see "Open"
    And I should see "Share"

  Scenario: Member can leave studio from profile page
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    And I click ".studios-list-item-wrapper[data-studio-id='2'] .projects-list-item--menu-btn"
    And I wait 500 milliseconds
    And I click "[data-action='leave'][data-studio-id='2']"
    And I wait 500 milliseconds
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element ".studios-list-item-wrapper[data-studio-id='2']" should not exist

  Scenario: User with no studios sees empty state
    Given I log in as "User2"
    And I am on "/app/user"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    Then the element "#no-studios" should be visible

  Scenario: Other users profile shows studios tab with public studios
    Given I log in as "User2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#studios-count"
    And I wait for AJAX to finish
    Then I should see "PublicStudio"
