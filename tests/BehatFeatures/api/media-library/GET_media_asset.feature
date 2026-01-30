Feature: Get media asset by ID

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are media categories:
      | id                                   | name                           | file_type | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | IMAGE     | 10       |
    And there are media assets:
      | id                                   | name      | extension | file_type | category                             | flavors    | downloads | author      |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 100       | Bob Schmidt |

  Scenario: Get media asset by ID
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "200"
    And the client response should contain "Dog Image"
    And the client response should contain "Bob Schmidt"

  Scenario: Get non-existent media asset returns 404
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets/00000000-0000-0000-0000-000000000000"
    Then the response status code should be "404"

  Scenario: Get specific project without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "406"
