@api @projects @reactions
Feature: Project reactions API

  Background:
    Given there are users:
      | id | name       | password |
      | 1  | Catrobat   | 123456   |
      | 2  | OtherUser  | 123456   |
      | 3  | ThirdUser  | 123456   |
    And there are projects:
      | id | name       | owned by  | visible |
      | 1  | Project1   | Catrobat  | true    |
      | 2  | Project2   | OtherUser | true    |
      | 3  | PrivatePrj | Catrobat  | false   |
    And there are project reactions:
      | project | user      | type      |
      | 2       | Catrobat  | thumbs_up |
      | 2       | ThirdUser | love      |

  # === POST /api/project/{id}/reaction - Add reaction ===

  Scenario: Add a thumbs_up reaction to a project
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "thumbs_up"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "201"
    And the client response should contain "thumbs_up"
    And the client response should contain "total"
    And the client response should contain "user_reactions"

  Scenario: Add a love reaction to a project
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "love"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "201"
    And the client response should contain "love"
    And the client response should contain "total"

  Scenario: Add a smile reaction to a project
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "smile"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "201"
    And the client response should contain "smile"
    And the client response should contain "total"

  Scenario: Add a wow reaction to a project
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "wow"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "201"
    And the client response should contain "wow"
    And the client response should contain "total"

  Scenario: Adding a reaction without authentication returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "thumbs_up"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "401"

  Scenario: Adding a reaction with invalid type returns 422
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "invalid_type"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "422"

  Scenario: Adding a reaction to a non-existent project returns 404
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "thumbs_up"
      }
    """
    And I request "POST" "/api/project/999/reaction"
    Then the response status code should be "404"

  Scenario: Adding a reaction to a non-visible project returns 404 for non-owner
    Given I use a valid JWT Bearer token for "OtherUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "thumbs_up"
      }
    """
    And I request "POST" "/api/project/3/reaction"
    Then the response status code should be "404"

  Scenario: Adding a duplicate reaction returns 409 (already exists)
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "thumbs_up"
      }
    """
    And I request "POST" "/api/project/2/reaction"
    Then the response status code should be "409"

  # === DELETE /api/project/{id}/reaction - Remove reaction ===

  Scenario: Remove a reaction from a project
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/2/reaction?type=thumbs_up"
    Then the response status code should be "204"

  Scenario: Removing a reaction without authentication returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/2/reaction?type=thumbs_up"
    Then the response status code should be "401"

  Scenario: Removing a reaction with invalid type returns 400
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/2/reaction?type=invalid_type"
    Then the response status code should be "400"

  Scenario: Removing a non-existent reaction returns 204 (idempotent)
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/1/reaction?type=thumbs_up"
    Then the response status code should be "204"

  Scenario: Removing a reaction from a non-existent project returns 404
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/999/reaction?type=thumbs_up"
    Then the response status code should be "404"

  # === GET /api/project/{id}/reactions - Get reaction summary ===

  Scenario: Get reaction summary for a project
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2/reactions"
    Then the response status code should be "200"
    And the client response should contain "total"
    And the client response should contain "thumbs_up"
    And the client response should contain "love"

  Scenario: Get reaction summary includes user reactions when authenticated
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2/reactions"
    Then the response status code should be "200"
    And the client response should contain "user_reactions"
    And the client response should contain "thumbs_up"

  Scenario: Get reaction summary for a project with no reactions
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/1/reactions"
    Then the response status code should be "200"
    And the client response should contain "total"

  Scenario: Get reaction summary for non-existent project returns 404
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/999/reactions"
    Then the response status code should be "404"

  Scenario: Get reaction summary for non-visible project returns 404 for non-owner
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/3/reactions"
    Then the response status code should be "404"

  Scenario: Owner can get reaction summary for non-visible project
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/3/reactions"
    Then the response status code should be "200"

  # === GET /api/project/{id}/reactions/users - Get reaction users ===

  Scenario: Get users who reacted to a project
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2/reactions/users"
    Then the response status code should be "200"
    And the client response should contain "data"
    And the client response should contain "has_more"

  Scenario: Get users with limit parameter
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2/reactions/users?limit=1"
    Then the response status code should be "200"
    And the client response should contain "data"
    And the client response should contain "next_cursor"

  Scenario: Get users filtered by reaction type
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2/reactions/users?type=love"
    Then the response status code should be "200"
    And the client response should contain "ThirdUser"
    And the client response should not contain "Catrobat"

  Scenario: Get reaction users for non-existent project returns 404
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/999/reactions/users"
    Then the response status code should be "404"

  Scenario: Get reaction users for non-visible project returns 404 for non-owner
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/3/reactions/users"
    Then the response status code should be "404"
