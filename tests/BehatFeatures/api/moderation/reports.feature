Feature: Moderation Reports API

  Background:
    Given there are users:
      | id | name      | verified |
      | 1  | Catrobat  | true     |
      | 2  | User2     | true     |
      | 3  | NewUser   | false    |
    And the users are created at:
      | name     | created_at          |
      | Catrobat | 2024-01-01 12:00:00 |
      | User2    | 2024-01-01 12:00:00 |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |
    And there are comments:
      | id                                   | project_id | user_id | text          | upload_date         | parent_id |
      | 00000000-0000-0000-0000-000000000010 | 1          | 1       | first comment | 2013-01-01 12:00:00 |           |
    And there are studios:
      | id | name    | description  |
      | 1  | studio1 | test studio  |

  # ---------------------------------------------------------------------------
  # POST /api/projects/{id}/report
  # ---------------------------------------------------------------------------

  Scenario: Report a project (authenticated)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam", "note": "This is spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "204"

  Scenario: Report a project requires authentication
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "401"

  Scenario: Report own project returns 403
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "403"

  Scenario: Report non-existent project returns 404
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/9999/report"
    Then the response status code should be "404"

  Scenario: Report already hidden project returns 409
    Given the projects are auto-hidden:
      | id |
      | 1  |
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "409"

  Scenario: Report with invalid category returns 400
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "nonexistent_cat"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "400"

  Scenario: Duplicate project report returns 409
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "204"
    # Report the same project again
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "copyright"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "409"

  Scenario: User with trust too low cannot report (403)
    Given I use a valid JWT Bearer token for "NewUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "403"

  # ---------------------------------------------------------------------------
  # POST /api/comments/{id}/report
  # ---------------------------------------------------------------------------

  Scenario: Report a comment (authenticated)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "204"

  Scenario: Report a comment requires authentication
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "401"

  Scenario: Report own comment returns 403
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "403"

  Scenario: Report non-existent comment returns 404
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000009999/report"
    Then the response status code should be "404"

  Scenario: Report already hidden comment returns 409
    Given the comments are auto-hidden:
      | id                                   |
      | 00000000-0000-0000-0000-000000000010 |
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "409"

  Scenario: Duplicate comment report returns 409
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "204"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "409"

  # ---------------------------------------------------------------------------
  # POST /api/users/{id}/report
  # ---------------------------------------------------------------------------

  Scenario: Report a user (authenticated)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "impersonation", "note": "Pretending to be someone else"}
      """
    When I request "POST" "/api/users/1/report"
    Then the response status code should be "204"

  Scenario: Report a user requires authentication
    When I request "POST" "/api/users/1/report"
    Then the response status code should be "401"

  Scenario: Report own user profile returns 403
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "impersonation"}
      """
    When I request "POST" "/api/users/1/report"
    Then the response status code should be "403"

  Scenario: Report non-existent user returns 404
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "impersonation"}
      """
    When I request "POST" "/api/users/9999/report"
    Then the response status code should be "404"

  Scenario: Report user with invalid category returns 400
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "copyright"}
      """
    When I request "POST" "/api/users/1/report"
    Then the response status code should be "400"

  # ---------------------------------------------------------------------------
  # POST /api/studios/{id}/report
  # ---------------------------------------------------------------------------

  Scenario: Report a studio (authenticated)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate_content"}
      """
    When I request "POST" "/api/studios/1/report"
    Then the response status code should be "204"

  Scenario: Report a studio requires authentication
    When I request "POST" "/api/studios/1/report"
    Then the response status code should be "401"

  Scenario: Report non-existent studio returns 404
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate_content"}
      """
    When I request "POST" "/api/studios/9999/report"
    Then the response status code should be "404"

  Scenario: Report studio with invalid category returns 400
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "sexual_content"}
      """
    When I request "POST" "/api/studios/1/report"
    Then the response status code should be "400"

  # ---------------------------------------------------------------------------
  # Whitelisted content cannot be reported
  # ---------------------------------------------------------------------------

  Scenario: Report on whitelisted (approved) project returns 403
    And the projects are approved:
      | id |
      | 1  |
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "403"

  Scenario: Report on project by approved user returns 403
    And the users are approved:
      | name     |
      | Catrobat |
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "403"

  Scenario: Report on approved user returns 403
    And the users are approved:
      | name     |
      | Catrobat |
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "impersonation"}
      """
    When I request "POST" "/api/users/1/report"
    Then the response status code should be "403"

  Scenario: Report on comment by approved user returns 403
    And the users are approved:
      | name     |
      | Catrobat |
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/report"
    Then the response status code should be "403"

  Scenario: Report on non-whitelisted content succeeds (204)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam", "note": "Non-whitelisted report"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "204"

  # ---------------------------------------------------------------------------
  # Hidden content filtering
  # ---------------------------------------------------------------------------

  Scenario: Auto-hidden project does not appear in project list API
    Given there are projects:
      | id | name     | owned by | description   |
      | 2  | project2 | Catrobat | hidden one    |
    And the projects are auto-hidden:
      | id |
      | 2  |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/user/1"
    Then the response status code should be "200"
    And the client response should contain "project1"
    And the client response should not contain "project2"

  Scenario: Auto-hidden comment does not appear in comments list API
    And there are comments:
      | id                                   | project_id | user_id | text            | upload_date         | parent_id |
      | 00000000-0000-0000-0000-000000000011 | 1          | 2       | visible comment | 2013-01-02 12:00:00 |           |
    And the comments are auto-hidden:
      | id                                   |
      | 00000000-0000-0000-0000-000000000010 |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/1/comments?limit=10"
    Then the response status code should be "200"
    And the client response should contain "visible comment"
    And the client response should not contain "first comment"

  Scenario: Hidden user does not appear in followers list API
    And there are followers:
      | name    | following |
      | User2   | Catrobat  |
      | NewUser | Catrobat  |
    And the users are profile-hidden:
      | name    |
      | NewUser |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/users/1/followers"
    Then the response status code should be "200"
    And the client response should contain "User2"
    And the client response should not contain "NewUser"

  # ---------------------------------------------------------------------------
  # End-to-end: report triggers auto-hide and creates notification for owner
  # ---------------------------------------------------------------------------

  Scenario: Admin report auto-hides project and owner receives moderation notification
    Given there are admins:
      | name  |
      | Admin |
    And I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam", "note": "Obvious spam project"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "204"
    And the project "project1" should be hidden
    Given I use a valid JWT Bearer token for "Catrobat"
    When I request "GET" "/api/notifications?type=all&limit=10"
    Then the response status code should be "200"
    And the client response should contain "has been hidden due to community reports"
