Feature: Delete media asset

  Background:
    Given there are admins:
      | name  | password | email                | id | role             |
      | Admin | 123456   | admin@pocketcode.org | 1  | ROLE_MEDIA_ADMIN |
    Given there are users:
      | name | password | id |
      | User | 123456   | 2  |
    And there are media categories:
      | id                                   | name                           | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | 10       |
    And there are media assets:
      | id                                   | name      | extension | file_type | category                             | flavors    | downloads | author |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 100       |        |

  Scenario: Delete a media asset
    Given I use a valid JWT Bearer token for "Admin"
    And I request "DELETE" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "204"

  Scenario: Delete a media asset without auth is not possible
    Given I request "DELETE" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "401"

  Scenario: Delete a media asset without permission is not possible
    Given I use a valid JWT Bearer token for "User"
    And I request "DELETE" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "403"

  Scenario: Deleting a non-existent media asset returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I request "DELETE" "/api/media/assets/00000000-0000-0000-0000-000000000000"
    Then the response status code should be "404"
