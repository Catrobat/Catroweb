@admin
Feature: From time to time it can be necessary to manually manipulate the database.

  Background:
    Given there are admins:
      | name     |
      | Adminius |

  Scenario: Special Updater updated the database
    Given I log in as "Adminius"
    And I am on "/admin/special_updater/list"
    And I wait for the page to be loaded
    Then I should see "Special Updater"
    And the element "#btn-update-database" should be visible
    When I click "#btn-update-database"
    And I wait for the page to be loaded
    Then I should see "Database has been successfully updated"