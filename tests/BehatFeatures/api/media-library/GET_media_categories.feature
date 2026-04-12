@api @media-library
Feature: Get media categories from the API

  Background:
    Given there are users:
      | id | name       | password |
      | 1  | AdminUser  | 123456   |
      | 2  | RegularUser| 123456   |
    And there are media categories:
      | id                                   | name                          | description                          | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals| media_library.category.animals_desc | 10       |
      | 550e8400-e29b-41d4-a716-446655440002 | media_library.category.sounds | media_library.category.sounds_desc  | 20       |
      | 550e8400-e29b-41d4-a716-446655440003 | media_library.category.space  | media_library.category.space_desc   | 5        |

  Scenario: Get all media categories
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/categories"
    Then the response status code should be "200"
    And the client response should contain "data"
    And the client response should contain "has_more"
    And the client response should contain "media_library.category.space"
    And the client response should contain "media_library.category.animals"
    And the client response should contain "media_library.category.sounds"

  Scenario: Get media categories with pagination
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/categories?limit=2&offset=0"
    Then the response status code should be "200"
    And the client response should contain "has_more"

  Scenario: Get media categories with invalid limit
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/categories?limit=-1"
    Then the response status code should be "400"

  Scenario: Get specific project without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/media/categories"
    Then the response status code should be "406"
