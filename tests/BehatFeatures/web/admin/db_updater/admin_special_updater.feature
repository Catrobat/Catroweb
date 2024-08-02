@admin
Feature: From time to time it can be necessary to manually manipulate the database.

  Background:
    Given there are admins:
      | name  |
      | Admin |

  Scenario: Special Updater can be used to manually update the database
    Given I log in as "Admin"
    And I am on "admin/system/db/special-updater/list"
    And I wait for the page to be loaded
    Then I should see "Special Updater"
    And the element "#btn-update-database" should be visible
    When I click "#btn-update-database"
    And I wait for the page to be loaded
    Then I should see "Database has been successfully updated"