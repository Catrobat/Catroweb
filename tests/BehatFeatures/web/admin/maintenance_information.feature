@admin
Feature: Maintenance information

  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |

    And there are maintenance information:
      | Id | Maintenance Start      | Maintenance End  | Additional Information | Icon  | Active | Title |
      | 1  | 2020-02-03             | 2020-05-04       |        test            | error | true   | test  |
      | 2  | 2020-02-03             | 2020-05-04       |        test2           | error | false  | test2 |
      | 3  | 2020-02-03             | 2020-05-04       |                        | error | false  | test3 |

  Scenario: List all maintenance information:
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/maintenance information/list"
    And I wait for the page to be loaded
    Then I should see the ready maintenance information table:
      | Feature Name | Active   | LTM Code                                                  | Maintenance Start | Maintenance End | Additional Information | Icon  | Actions |
      | test         | yes      | maintenanceinformations.maintenance_information.feature_1 | February 3, 2020  | May 4, 2020     |        test            | error | Edit    |
      | test2        | no       | maintenanceinformations.maintenance_information.feature_2 | February 3, 2020  | May 4, 2020     |        test2           | error | Edit    |
      | test3        | no       | maintenanceinformations.maintenance_information.feature_3 | February 3, 2020  | May 4, 2020     |                        | error | Edit    |

  Scenario: There should be maintenance information
    Given I am on "/app/"
    And I wait for the page to be loaded
    Then the element "#viewID_1" should be visible
    And I should see "test"
    And I should not see "test2"
    When I click ".expand-button"
    Then I should see "Additional information:"


  Scenario: There should be no maintenance information if closed
    Given I am on "/app/"
    And I wait for the page to be loaded
    Then the element "#viewID_1" should be visible
    When I click ".close-button"
    Then the element "#viewID_1" should not be visible