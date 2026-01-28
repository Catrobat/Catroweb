Feature: Update media category

  Background:
    Given there are admins:
      | name  | password | email                | id | role             |
      | Admin | 123456   | admin@pocketcode.org | 1  | ROLE_MEDIA_ADMIN |
    And there are media categories:
      | id                                   | name                           | description                         | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | media_library.category.animals_desc | 10       |

  Scenario: Update a media category
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals_updated",
        "description": "media_library.category.animals_desc_updated",
        "priority": 15
      }
    """
    And I request "PATCH" "/api/media/categories/550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "200"
    And the client response should contain "media_library.category.animals_updated"

  Scenario: Update media category with invalid param
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": 3
      }
    """
    And I request "PATCH" "/api/media/categories/550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "400"

  Scenario: Update media category with invalid accept header
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "invalid"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals_updated"
      }
    """
    And I request "PATCH" "/api/media/categories/550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "406"

  Scenario: Update media category with missing content type
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals_updated"
      }
    """
    And I request "PATCH" "/api/media/categories/550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "415"

  Scenario: Updating a non-existent media category returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals_updated"
      }
    """
    And I request "PATCH" "/api/media/categories/00000000-0000-0000-0000-000000000000"
    Then the response status code should be "404"
