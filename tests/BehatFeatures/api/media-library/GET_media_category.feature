Feature: Get media category by ID

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are media categories:
      | id                                   | name                           | description                          | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | media_library.category.animals_desc | 10       |

  Scenario: Get media category by ID
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/categories/550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "200"
    And the client response should contain "media_library.category.animals"

  Scenario: Get non-existent media category returns 404
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/categories/00000000-0000-0000-0000-000000000000"
    Then the response status code should be "404"

  Scenario: Get specific project without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/media/categories/550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "406"
