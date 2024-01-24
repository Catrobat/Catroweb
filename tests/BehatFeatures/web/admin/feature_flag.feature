@admin
Feature: Admin featured projects

Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |

Scenario: List all Feature Flags:
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/featureflag/list"
    And I wait for the page to be loaded
    Then I should see "Test-Flag"

Scenario: Test feature is disabled:
    Given I am on "/app/featureflag/test"
    And I wait for the page to be loaded
    Then I should see "Test feature is disabled!"

Scenario: Overriding test feature with header true:
    Given I am on the page "/app/featureflag/test" with header "X-Feature-Flag-Test-Flag" equal to "1"
    And I wait for the page to be loaded
    Then I should see "Test feature is enabled!"

Scenario: Overriding test feature with header false:
    Given I am on the page "/app/featureflag/test" with header "X-Feature-Flag-Test-Flag" equal to "0"
    And I wait for the page to be loaded
    Then I should see "Test feature is disabled!"

Scenario: List Studio Link Feature Flags:
   Given I log in as "Admin" with the password "123456"
   And I am on "/admin/featureflag/list"
   Then I should see "Sidebar-Studio-Link-Feature"







