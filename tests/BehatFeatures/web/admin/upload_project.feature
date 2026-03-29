@admin
Feature: Admin project upload page
  As an admin I want to upload .catrobat project files on behalf of users.

  Background:
    Given there are admins:
      | name  | password | email                | id |
      | Admin | 123456   | admin@pocketcode.org | 1  |
    And there are users:
      | name     | password | email               | id |
      | Catrobat | 123456   | dev1@pocketcode.org | 2  |

  Scenario: Admin can access the upload page
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Upload Project"

  Scenario: Non-admin user cannot access the upload page
    Given I log in as "Catrobat" with the password "123456"
    And I GET "/admin/projects/upload/list"
    Then the client response should contain "Access Denied"

  Scenario: Upload form displays required fields and labels
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Maximum file size"
    And I should see "100 MB"
    And I should see an "#project_file" element
    And I should see an "#username" element
    And I should see an "#flavor" element
    And I should see "Owner (Username)"
    And I should see "Project File (.catrobat)"

  Scenario: Upload form shows all flavor options
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "pocketcode"
    And I should see "arduino"
    And I should see "luna"
    And I should see "embroidery"

  Scenario: Upload form has a private project checkbox
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Private project"
    And I should see "If checked, the project will not be visible to other users."

  Scenario: Upload form has description text
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/projects/upload/list"
    And I wait for the page to be loaded
    Then I should see "Upload a"
    And I should see "project file on behalf of a user."
    And I should see "The project name, description, and credits will be extracted from the uploaded file."
    And I should see "The project will be uploaded as this user."
