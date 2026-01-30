Feature: Update media asset

  Background:
    Given there are admins:
      | name  | password | email                | id | role             |
      | Admin | 123456   | admin@pocketcode.org | 1  | ROLE_MEDIA_ADMIN |
    And there are users:
      | name | password | id |
      | User | 123456   | 2  |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
    And there are media categories:
      | id                                   | name                           | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | 10       |
      | 550e8400-e29b-41d4-a716-446655440002 | media_library.category.sounds  | 20       |
    And there are media assets:
      | id                                   | name      | extension | file_type | category                             | flavors    | downloads | author |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode | 100       |        |

  Scenario: Update a media asset
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "Dog Image Updated",
        "description": "Updated description",
        "category_id": "550e8400-e29b-41d4-a716-446655440002",
        "author": "Catrobat",
        "active": false
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "200"
    And the client response should contain "Dog Image Updated"

  Scenario: Update a media asset with invalid param
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": 3
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "400"

  Scenario: Update a media asset with invalid category id
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "category_id": "00000000-0000-0000-0000-000000000000"
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "422"

  Scenario: Update a media asset with invalid accept header
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "invalid"
    And I have the following JSON request body:
    """
      {
        "name": "Dog Image Updated"
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "406"

  Scenario: Update a media asset with missing content type
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "Dog Image Updated"
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "415"

  Scenario: Update a media asset without auth is not possible
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "Dog Image Updated",
        "description": "Updated description",
        "category_id": "550e8400-e29b-41d4-a716-446655440002",
        "author": "Catrobat",
        "active": false
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "401"

  Scenario: Update a media asset without permission must not be possible
    Given I use a valid JWT Bearer token for "User"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "Dog Image Updated",
        "description": "Updated description",
        "category_id": "550e8400-e29b-41d4-a716-446655440002",
        "author": "Catrobat",
        "active": false
      }
    """
    And I request "PATCH" "/api/media/assets/650e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "403"

  Scenario: Updating a non-existent media asset returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "name": "Dog Image Updated"
      }
    """
    And I request "PATCH" "/api/media/assets/00000000-0000-0000-0000-000000000000"
    Then the response status code should be "404"
