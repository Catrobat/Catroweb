Feature: Comments API

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |
    And there are comments:
      | id | project_id | user_id | text            | upload_date         | parent_id |
      | 10 | 1          | 1       | first comment   | 2013-01-01 12:00:00 |           |
      | 11 | 1          | 2       | second comment  | 2013-01-02 12:00:00 |           |
      | 12 | 1          | 2       | third comment   | 2013-01-03 12:00:00 |           |
      | 20 | 1          | 2       | reply comment 1 | 2013-01-04 12:00:00 | 10        |
      | 21 | 1          | 1       | reply comment 2 | 2013-01-05 12:00:00 | 10        |

  # ---------------------------------------------------------------------------
  # GET /api/project/{id}/comments
  # ---------------------------------------------------------------------------

  Scenario: Get project comments with cursor pagination
    Given I request "GET" "/api/project/1/comments?limit=2"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "has_more"
    And the client response should contain "next_cursor"
    And the client response should contain "first comment"
    And the client response should contain "second comment"

  Scenario: Get project comments returns 404 for non-existent project
    Given I request "GET" "/api/project/9999/comments"
    Then the response status code should be "404"

  Scenario: Get project comments returns 400 for an invalid cursor
    Given I request "GET" "/api/project/1/comments?cursor=!!!invalid!!!"
    Then the response status code should be "400"

  # ---------------------------------------------------------------------------
  # GET /api/comments/{id}/replies
  # ---------------------------------------------------------------------------

  Scenario: Get comment replies with cursor pagination
    Given I request "GET" "/api/comments/10/replies?limit=1"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "reply comment 1"

  Scenario: Get replies returns 404 for non-existent comment
    Given I request "GET" "/api/comments/9999/replies"
    Then the response status code should be "404"

  Scenario: Public caller cannot access replies for a hidden comment
    Given the comments are auto-hidden:
      | id |
      | 10 |
    When I request "GET" "/api/comments/10/replies?limit=1"
    Then the response status code should be "404"

  Scenario: Owner can access replies for a hidden comment
    Given the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "Catrobat"
    When I request "GET" "/api/comments/10/replies?limit=1"
    Then the response status code should be "200"

  Scenario: Admin can access replies for a hidden comment
    Given there are admins:
      | name  |
      | Admin |
    And the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "Admin"
    When I request "GET" "/api/comments/10/replies?limit=1"
    Then the response status code should be "200"

  # ---------------------------------------------------------------------------
  # GET /api/comments/{id}/translation
  # ---------------------------------------------------------------------------

  Scenario: Public caller cannot translate a hidden comment
    Given the comments are auto-hidden:
      | id |
      | 10 |
    When I request "GET" "/api/comments/10/translation?target_language=de"
    Then the response status code should be "404"

  Scenario: Owner can translate a hidden comment
    Given the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "Catrobat"
    When I request "GET" "/api/comments/10/translation?target_language=de"
    Then the response status code should be "200"

  Scenario: Admin can translate a hidden comment
    Given there are admins:
      | name  |
      | Admin |
    And the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "Admin"
    When I request "GET" "/api/comments/10/translation?target_language=de"
    Then the response status code should be "200"

  # ---------------------------------------------------------------------------
  # POST /api/project/{id}/comments
  # ---------------------------------------------------------------------------

  Scenario: Create a comment
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {
        "message": "new comment"
      }
      """
    When I request "POST" "/api/project/1/comments"
    Then the response status code should be "201"
    And the client response should contain "new comment"
    And the client response should contain "rendered"

  Scenario: Create a comment requires authentication
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {
        "message": "unauthenticated comment"
      }
      """
    When I request "POST" "/api/project/1/comments"
    Then the response status code should be "401"

  Scenario: Create a comment returns 404 for non-existent project
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {
        "message": "hello"
      }
      """
    When I request "POST" "/api/project/9999/comments"
    Then the response status code should be "404"

  Scenario: Non-owner cannot reply to a hidden parent comment
    Given the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {
        "message": "hidden parent reply should fail",
        "parent_id": 10
      }
      """
    When I request "POST" "/api/project/1/comments"
    Then the response status code should be "404"

  Scenario: Owner can reply to a hidden parent comment
    Given the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {
        "message": "owner hidden parent reply",
        "parent_id": 10
      }
      """
    When I request "POST" "/api/project/1/comments"
    Then the response status code should be "201"

  Scenario: Admin can reply to a hidden parent comment
    Given there are admins:
      | name  |
      | Admin |
    And the comments are auto-hidden:
      | id |
      | 10 |
    And I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {
        "message": "admin hidden parent reply",
        "parent_id": 10
      }
      """
    When I request "POST" "/api/project/1/comments"
    Then the response status code should be "201"

  # ---------------------------------------------------------------------------
  # DELETE /api/comments/{id}
  # ---------------------------------------------------------------------------

  Scenario: Delete a comment
    Given I use a valid JWT Bearer token for "Catrobat"
    When I request "DELETE" "/api/comments/10"
    Then the response status code should be "204"
    And the response content must be empty

  Scenario: Delete a comment requires authentication
    When I request "DELETE" "/api/comments/10"
    Then the response status code should be "401"

  Scenario: Delete a comment returns 404 for non-existent comment
    Given I use a valid JWT Bearer token for "Catrobat"
    When I request "DELETE" "/api/comments/9999"
    Then the response status code should be "404"

  Scenario: Delete a comment by another user returns 403
    Given I use a valid JWT Bearer token for "User2"
    When I request "DELETE" "/api/comments/10"
    Then the response status code should be "403"

  # ---------------------------------------------------------------------------
  # POST /api/comments/{id}/report
  # ---------------------------------------------------------------------------

  Scenario: Report a comment
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/comments/11/report"
    Then the response status code should be "204"

  Scenario: Report a comment requires authentication
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/comments/11/report"
    Then the response status code should be "401"

  Scenario: Report a comment returns 404 for non-existent comment
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/comments/9999/report"
    Then the response status code should be "404"
