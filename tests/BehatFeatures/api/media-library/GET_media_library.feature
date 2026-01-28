Feature: Get media library overview

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
    And there are media categories:
      | id                                   | name                           | description                         | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | media_library.category.animals_desc | 10       |
      | 550e8400-e29b-41d4-a716-446655440002 | media_library.category.sounds  | media_library.category.sounds_desc  | 20       |
      | 550e8400-e29b-41d4-a716-446655440003 | media_library.category.space   | media_library.category.space_desc   | 5        |
    And there are media assets:
      | id                                   | name       | extension | file_type | category                             | flavors    | downloads | author      |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image  | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 100       | Bob Schmidt |
      | 650e8400-e29b-41d4-a716-446655440002 | Meow Sound | mp3       | SOUND     | 550e8400-e29b-41d4-a716-446655440002 | luna       | 25        |             |

  Scenario: Get media library overview with preview assets
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/library?assets_per_category=1"
    Then the response status code should be "200"
    And the client response should contain "categories"
    And the client response should contain "media_library.category.animals"
    And the client response should contain "Dog Image"

  Scenario: Filter media library by file type
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/library?file_type=IMAGE"
    Then the response status code should be "200"
    And the client response should contain "media_library.category.animals"
    And the client response should contain "media_library.category.sounds"
    And the client response should not contain "Meow Sound"

  Scenario: Search media library by category name
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/library?search=space"
    Then the response status code should be "200"
    And the client response should contain "media_library.category.space"
    And the client response should not contain "media_library.category.animals"

  Scenario: Search media library by asset name
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/library?search=Dog"
    Then the response status code should be "200"
    And the client response should contain "media_library.category.animals"
    And the client response should contain "Dog Image"
    And the client response should not contain "Meow Sound"

  Scenario: Search media library with no results
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/library?search=unknown"
    Then the response status code should be "200"
    And the client response should contain "categories"
    And the client response should not contain "media_library.category.animals"

  Scenario: Request media library with invalid assets per category
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/library?assets_per_category=0"
    Then the response status code should be "400"

  Scenario: Search media library with no results
    And I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/media/library"
    Then the response status code should be "406"
