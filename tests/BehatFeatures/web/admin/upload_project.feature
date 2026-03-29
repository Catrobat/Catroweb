@web @admin
Feature: Admin project upload page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are admins:
      | name  |
      | Admin |

  Scenario: Admin can access the upload page
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Upload Project"

  Scenario: Non-admin cannot access the upload page
    Given I am logged in as normal user
    When I GET "/admin/projects/upload/list"
    Then the client response should contain "Access Denied"

  Scenario: Upload form has required fields
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Maximum file size: 100 MB"
    And I should see an "#project_file" element
    And I should see an "#username" element
    And I should see an "#flavor" element

  Scenario: Flavor options are available
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "pocketcode"
    And I should see "arduino"
    And I should see "embroidery"
    And I should see "luna"

  Scenario: Private checkbox is available
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Private project"
    And I should see an "#private" element
