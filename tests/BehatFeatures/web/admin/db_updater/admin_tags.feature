@admin
Feature: The admin tags view provides a detailed list about all tags and allows to update them.

  Background:
    Given there are admins:
      | name  |
      | Admin |
    Given there are tags:
      | id | internal_title | enabled |
      | 1  | game           | 1       |
      | 2  | animation      | 1       |
    And there are projects:
      | id | name    | tags           |
      | 1  | Minions | game,animation |
      | 2  | Galaxy  | game           |

  Scenario: All tags should be displayed with some stats
    Given I log in as "Admin"
    And I am on "/admin/tags/list"
    And I wait for the page to be loaded
    Then I should see "Tags"
    And I should see the tags table:
      | Internal Title | Enabled | Projects with tag |
      | game           | yes     | 2                 |
      | animation      | yes     | 1                 |

  Scenario: Tags can be updated
    Given I log in as "Admin"
    And I am on "/admin/tags/list"
    And I wait for the page to be loaded
    Then I should see "Update tags"
    And I should see "Step-by-step guide"
    And I should not see "story"
    And the element "#btn-update-tags" should be visible
    And there should be "2" tags in the database
    When I click "#btn-update-tags"
    And I wait for the page to be loaded
    Then I should see "Tags have been successfully updated"
    And there should be "8" tags in the database
    And I am on "/admin/tags/list"
    And I wait for the page to be loaded
    And I should see "Tags"
    And I should see the tags table:
      | Internal Title | Enabled | Projects with tag |
      | game           | yes     | 2                 |
      | animation      | yes     | 1                 |
      | story          | yes     | 0                 |
