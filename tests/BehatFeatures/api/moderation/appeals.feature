Feature: Moderation Appeals API

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And the users are created at:
      | name     | created_at          |
      | Catrobat | 2024-01-01 12:00:00 |
      | User2    | 2024-01-01 12:00:00 |
    And there are admins:
      | name  |
      | Admin |
    And there are projects:
      | id | name     | owned by | description   |
      | 1  | project1 | Catrobat | mydescription |
      | 2  | project2 | User2    | other project |
    And there are comments:
      | id | project_id | user_id | text          | upload_date         | parent_id |
      | 10 | 1          | 1       | first comment | 2013-01-01 12:00:00 |           |
    And there are studios:
      | id | name    | description  |
      | 1  | studio1 | test studio  |

  # ---------------------------------------------------------------------------
  # POST /api/projects/{id}/appeal
  # ---------------------------------------------------------------------------

  Scenario: Appeal a hidden project (authenticated owner)
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "This project was hidden by mistake"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "201"

  Scenario: Appeal a project requires authentication
    Given the project "project1" is auto-hidden
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "401"

  Scenario: Appeal a project that is not hidden returns 400
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "400"

  Scenario: Non-owner cannot appeal a project (403)
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "This should be visible"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "403"

  Scenario: Appeal non-existent project returns 404
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review"}
      """
    When I request "POST" "/api/projects/9999/appeal"
    Then the response status code should be "404"

  Scenario: Duplicate pending appeal returns 409
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "First appeal"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "201"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Second appeal attempt"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "409"

  # ---------------------------------------------------------------------------
  # POST /api/comments/{id}/appeal
  # ---------------------------------------------------------------------------

  Scenario: Appeal a hidden comment (authenticated owner)
    Given the comment 10 is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "My comment was appropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/appeal"
    Then the response status code should be "201"

  Scenario: Appeal a comment requires authentication
    Given the comment 10 is auto-hidden
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/appeal"
    Then the response status code should be "401"

  Scenario: Appeal a comment that is not hidden returns 400
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/appeal"
    Then the response status code should be "400"

  Scenario: Non-owner cannot appeal a comment (403)
    Given the comment 10 is auto-hidden
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "This comment is fine"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000010/appeal"
    Then the response status code should be "403"

  Scenario: Appeal non-existent comment returns 404
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000009999/appeal"
    Then the response status code should be "404"

  # ---------------------------------------------------------------------------
  # POST /api/users/{id}/appeal
  # ---------------------------------------------------------------------------

  Scenario: Appeal own hidden user profile
    Given the user "Catrobat" profile is hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "My profile was hidden unfairly"}
      """
    When I request "POST" "/api/users/1/appeal"
    Then the response status code should be "201"

  Scenario: Appeal user profile requires authentication
    Given the user "Catrobat" profile is hidden
    When I request "POST" "/api/users/1/appeal"
    Then the response status code should be "401"

  Scenario: Cannot appeal another users profile (403)
    Given the user "Catrobat" profile is hidden
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "This user is fine"}
      """
    When I request "POST" "/api/users/1/appeal"
    Then the response status code should be "403"

  # ---------------------------------------------------------------------------
  # POST /api/studios/{id}/appeal
  # ---------------------------------------------------------------------------

  Scenario: Appeal a hidden studio
    Given there are studio users:
      | studio_id | user     | role  | status |
      | 1         | Catrobat | admin | active |
    And the studio "studio1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "This studio complies with guidelines"}
      """
    When I request "POST" "/api/studios/1/appeal"
    Then the response status code should be "201"

  Scenario: Appeal a studio requires authentication
    Given the studio "studio1" is auto-hidden
    When I request "POST" "/api/studios/1/appeal"
    Then the response status code should be "401"

  Scenario: Appeal a studio that is not hidden returns 400
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review"}
      """
    When I request "POST" "/api/studios/1/appeal"
    Then the response status code should be "400"

  Scenario: Non-owner cannot appeal a hidden studio
    Given there are studio users:
      | studio_id | user     | role  | status |
      | 1         | Catrobat | admin | active |
    And the studio "studio1" is auto-hidden
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "I should not be allowed"}
      """
    When I request "POST" "/api/studios/1/appeal"
    Then the response status code should be "403"

  Scenario: Approving an appeal rejects new reports but leaves accepted reports unchanged
    Given there are moderation reports:
      | id                                   | reporter | content_type | content_id | category | state    | created_at           | resolved_at          | resolved_by |
      | 00000000-0000-0000-0000-000000000301 | User2    | project      | 1          | spam     | accepted | 2024-01-01 09:00:00 | 2024-01-01 09:10:00 | User2       |
      | 00000000-0000-0000-0000-000000000302 | Admin    | project      | 1          | spam     | new      | 2024-01-01 09:11:00 |                      |             |
    And the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please restore this project"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "approve"}
      """
    When I request "PUT" "/api/moderation/appeals/00000000-0000-0000-0000-000000000001/resolve"
    Then the response status code should be "200"
    And moderation report "00000000-0000-0000-0000-000000000301" should have state "accepted"
    And moderation report "00000000-0000-0000-0000-000000000302" should have state "rejected"
    And the project "project1" should be visible

  Scenario: Re-appeal allowed after prior rejection
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Initial appeal"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "reject"}
      """
    When I request "PUT" "/api/moderation/appeals/00000000-0000-0000-0000-000000000001/resolve"
    Then the response status code should be "200"
    # Content must be re-hidden for a second appeal to be valid
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Second appeal after rejection"}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "201"

  Scenario: Appeal with empty reason returns 400
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": ""}
      """
    When I request "POST" "/api/projects/1/appeal"
    Then the response status code should be "400"
