@admin
Feature: The admin extensions view provides a detailed list about all extensions and allows to update them.

  Background:
    Given there are admins:
      | name     |
      | Adminius |
    Given there are extensions:
      | id | internal_title | enabled |
      | 1  | arduino        | 1       |
      | 2  | drone          | 1       |
    And there are projects:
      | id | name    | extensions    |
      | 1  | Minions | arduino       |
      | 2  | Galaxy  | arduino,drone |

  Scenario: All extensions should be displayed with some stats
    Given I log in as "Adminius"
    And I am on "/admin/extensions/list"
    And I wait for the page to be loaded
    Then I should see "extensions"
    And I should see the extensions table:
      | Internal Title | Enabled | Projects with extension |
      | arduino        | yes     | 2                       |
      | drone          | yes     | 1                       |

  Scenario: Extensions can be updated
    Given I log in as "Adminius"
    And I am on "/admin/extensions/list"
    And I wait for the page to be loaded
    Then I should see "Update extensions"
    And I should see "Step-by-step guide"
    And I should not see "phiro"
    And the element "#btn-update-extensions" should be visible
    And there should be "2" extensions in the database
    When I click "#btn-update-extensions"
    And I wait for the page to be loaded
    Then I should see "Extensions have been successfully updated"
    Then there should be "7" extensions in the database
    Then I should see "Extensions"
    And I should see the extensions table:
      | Internal Title | Enabled | Projects with extension |
      | arduino        | yes     | 2                       |
      | drone          | yes     | 1                       |
      | phiro          | yes     | 0                       |
