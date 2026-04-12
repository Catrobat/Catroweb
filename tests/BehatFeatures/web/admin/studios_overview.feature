@admin
Feature: Admin studios overview

  Background:
    Given there are admins:
      | id | name  | password | email                | approved |
      | 1  | Edmin | 123456   | admin@pocketcode.org | true     |
    And there are studios:
      | id                                   | name       | description   | created_on          |
      | 11111111-1111-4111-8111-111111111111 | Studio One | First Studio  | 2024-01-01 10:00:00 |
      | 22222222-2222-4222-8222-222222222222 | Studio Two | Second Studio | 2024-01-02 10:00:00 |

  Scenario: Admin can access studios overview
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/studio/list"
    And I wait for the page to be loaded
    Then I should see "Studio One"
    And I should see "Studio Two"

  Scenario: Admin can open studio delete action
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/studio/11111111-1111-4111-8111-111111111111/delete"
    And I wait for the page to be loaded
    Then I should see "Delete"
    And I should see "Studio One"

  Scenario: Dashboard shows studios stats tile
    Given I log in as "Edmin" with the password "123456"
    And I am on "/admin/dashboard"
    And I wait for the page to be loaded
    Then I should see "All studios"
