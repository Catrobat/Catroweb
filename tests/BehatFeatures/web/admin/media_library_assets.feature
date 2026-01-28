Feature: Admin media library assets

  Background:
    Given there are admins:
      | name  | password | email                | id |
      | Admin | 123456   | admin@pocketcode.org | 1  |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
    And there are media categories:
      | id                                   | name    | description   | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | Animals | Animal assets | 10       |
    And there are media assets:
      | id                                   | name      | extension | file_type | category                             | flavors    | downloads | author |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 10        |        |

  Scenario: List media library assets
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-library/asset/list"
    And I wait for the page to be loaded
    Then I should see "Media Library Assets"
    And I should see "Dog Image"

  Scenario: Open create media library asset form
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/media-library/asset/create"
    And I wait for the page to be loaded
    Then I should see "Name"
    And I should see "File"
    And I should see "Category"
    And I should see "File type"
