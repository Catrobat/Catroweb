Feature: Admin media library categories

  Background:
    Given there are admins:
      | name  | password | email                | id |
      | Admin | 123456   | admin@pocketcode.org | 1  |
    And there are media categories:
      | id                                   | name    | description   | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | Animals | Animal assets | 10       |

  Scenario: List media library categories
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-library/category/list"
    And I wait for the page to be loaded
    Then I should see "Media Library Categories"
    And I should see "Animals"

  Scenario: Open create media library category form
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-library/category/create"
    And I wait for the page to be loaded
    Then I should see "Name"
    And I should see "Description"
    And I should see "Priority"
