Feature: Create media category

  Background:
    Given there are admins:
      | name  | password | email                | id | role             |
      | Admin | 123456   | admin@pocketcode.org | 1  | ROLE_MEDIA_ADMIN |

  Scenario: Create a media category
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals",
        "description": "media_library.category.animals_desc",
        "priority": 10
      }
    """
    And I request "POST" "/api/media/categories"
    Then the response status code should be "201"
    And the client response should contain "media_library.category.animals"

  Scenario: Create a media category with missing name
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "description": "media_library.category.animals_desc",
        "priority": 10
      }
    """
    And I request "POST" "/api/media/categories"
    Then the response status code should be "400"

  Scenario: Create a media category with invalid accept header
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "invalid"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals",
        "description": "media_library.category.animals_desc",
        "priority": 10
      }
    """
    And I request "POST" "/api/media/categories"
    Then the response status code should be "406"

  Scenario: Create a media category with missing content type
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals",
        "description": "media_library.category.animals_desc",
        "priority": 10
      }
    """
    And I request "POST" "/api/media/categories"
    Then the response status code should be "415"

  Scenario: Creating a media category without authentication fails
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "media_library.category.animals",
        "description": "media_library.category.animals_desc",
        "priority": 10
      }
    """
    And I request "POST" "/api/media/categories"
    Then the response status code should be "401"
