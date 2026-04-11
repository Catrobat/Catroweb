Feature: Verified account enforcement for write API endpoints

  Background:
    Given there are users:
      | id | name           |
      | 1  | VerifiedUser   |
      | 2  | UnverifiedUser |
    And the users are created at:
      | name           | created_at          |
      | VerifiedUser   | 2024-01-01 12:00:00 |
      | UnverifiedUser | 2024-01-01 12:00:00 |
    And the users are unverified:
      | name           |
      | UnverifiedUser |
    And there are projects:
      | id | name     | owned by     | description   |
      | 1  | project1 | VerifiedUser | mydescription |
    And there are comments:
      | id | project_id | user_id | text          | upload_date         | parent_id |
      | 10 | 1          | 1       | first comment | 2013-01-01 12:00:00 |           |

  # ---------------------------------------------------------------------------
  # Unverified user blocked from write endpoints
  # ---------------------------------------------------------------------------

  Scenario: Unverified user POST comment returns 403
    Given I use a valid JWT Bearer token for "UnverifiedUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello world"}
      """
    When I request "POST" "/api/projects/1/comments"
    Then the response status code should be "403"

  Scenario: Unverified user POST report returns 403
    Given I use a valid JWT Bearer token for "UnverifiedUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "403"

  # ---------------------------------------------------------------------------
  # Unverified user allowed on read endpoints
  # ---------------------------------------------------------------------------

  Scenario: Unverified user GET project is allowed
    Given I use a valid JWT Bearer token for "UnverifiedUser"
    When I request "GET" "/api/projects/1"
    Then the response status code should be "200"

  # ---------------------------------------------------------------------------
  # Authentication endpoints are exempt
  # ---------------------------------------------------------------------------

  Scenario: Unverified user POST authentication is allowed
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"username": "UnverifiedUser", "password": "123456"}
      """
    When I request "POST" "/api/authentication"
    Then the response status code should be "200"

  # ---------------------------------------------------------------------------
  # Verified user allowed on write endpoints
  # ---------------------------------------------------------------------------

  Scenario: Verified user POST comment is allowed
    Given I use a valid JWT Bearer token for "VerifiedUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello world"}
      """
    When I request "POST" "/api/projects/1/comments"
    Then the response status code should be "201"
