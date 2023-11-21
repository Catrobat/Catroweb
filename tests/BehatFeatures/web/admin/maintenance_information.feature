@admin
Feature: Maintenance information

  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |

    And there are maintenance information:
      | Id | Maintenance Start      | Maintenance End  | Additional Information | Icon  | Active | Title |
      | 1  | 2020-02-03             | 2020-05-04       |                        | error | true   | test  |


  Scenario: List all programs:
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/maintenanceinformation/list"
    And I wait for the page to be loaded
    Then I should see the following not approved projects:
      | Feature Name | Active | LTM Code                                                  | Maintenance Start | Maintenance End | Additional Information | Icon  | Actions |
      | test         | yes    | maintenanceinformations.maintenance_information.feature_1 | February 3, 2020  | May 4, 2020     |                        | error | Edit    |
