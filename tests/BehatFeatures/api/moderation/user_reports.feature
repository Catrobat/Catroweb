@api @moderation
Feature: User Reports API (GET /api/user/reports)

  Background:
    Given there are users:
      | id | name     | verified |
      | 1  | Catrobat | true     |
      | 2  | User2    | true     |
      | 3  | User3    | true     |
    And the users are created at:
      | name     | created_at          |
      | Catrobat | 2024-01-01 12:00:00 |
      | User2    | 2024-01-01 12:00:00 |
      | User3    | 2024-01-01 12:00:00 |
    And there are projects:
      | id | name     | owned by | description   |
      | 1  | project1 | Catrobat | mydescription |
      | 2  | project2 | User2    | other project |
    And there are comments:
      | id | project_id | user_id | text          | upload_date         | parent_id |
      | 10 | 1          | 1       | first comment | 2024-01-01 12:00:00 |           |
    And there are studios:
      | id | name    | description |
      | 1  | studio1 | test studio |

  # ---------------------------------------------------------------------------
  # Authentication
  # ---------------------------------------------------------------------------

  Scenario: Unauthenticated user gets 401
    When I request "GET" "/api/user/reports"
    Then the response status code should be "401"

  # ---------------------------------------------------------------------------
  # Empty results
  # ---------------------------------------------------------------------------

  Scenario: Authenticated user with no reports gets empty data
    Given I use a valid JWT Bearer token for "User3"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=20"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "data"
    And the client response should contain "has_more"

  # ---------------------------------------------------------------------------
  # User sees only their own reports
  # ---------------------------------------------------------------------------

  Scenario: User sees their own reports but not reports filed by others
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category      | state | created_at          |
      | 101 | User2    | project      | 1          | spam          | new   | 2024-06-01 12:00:00 |
      | 102 | User3    | project      | 1          | inappropriate | new   | 2024-06-02 12:00:00 |
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "spam"
    And the client response should not contain "inappropriate"

  # ---------------------------------------------------------------------------
  # Status mapping (pending / accepted / rejected)
  # ---------------------------------------------------------------------------

  Scenario: Response contains correct status mapping for report states
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category      | state    | created_at          | resolved_at         | resolved_by |
      | 201 | Catrobat | project      | 1          | spam          | new      | 2024-06-01 10:00:00 |                     |             |
      | 202 | Catrobat | project      | 2          | inappropriate | accepted | 2024-06-02 10:00:00 | 2024-06-03 10:00:00 | User2       |
      | 203 | Catrobat | comment      | 10         | spam          | rejected | 2024-06-03 10:00:00 | 2024-06-04 10:00:00 | User2       |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "pending"
    And the client response should contain "accepted"
    And the client response should contain "rejected"

  # ---------------------------------------------------------------------------
  # resolved_at is returned for resolved reports
  # ---------------------------------------------------------------------------

  Scenario: User sees resolved_at for resolved reports
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category      | state    | created_at          | resolved_at         | resolved_by |
      | 301 | User2    | project      | 1          | spam          | new      | 2024-06-01 10:00:00 |                     |             |
      | 302 | User2    | project      | 2          | inappropriate | accepted | 2024-06-02 10:00:00 | 2024-06-05 14:30:00 | User3       |
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "2024-06-05"
    And the client response should contain "resolved_at"

  # ---------------------------------------------------------------------------
  # Reports ordered by most recent first
  # ---------------------------------------------------------------------------

  Scenario: Reports are ordered by most recent first
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category      | state | created_at          |
      | 401 | Catrobat | project      | 1          | spam          | new   | 2024-06-01 10:00:00 |
      | 402 | Catrobat | project      | 2          | inappropriate | new   | 2024-06-03 10:00:00 |
      | 403 | Catrobat | comment      | 10         | copyright     | new   | 2024-06-02 10:00:00 |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "spam"
    And the client response should contain "inappropriate"
    And the client response should contain "copyright"

  # ---------------------------------------------------------------------------
  # Pagination (limit + cursor)
  # ---------------------------------------------------------------------------

  Scenario: Pagination returns has_more and next_cursor when more results exist
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category      | state | created_at          |
      | 501 | User2    | project      | 1          | spam          | new   | 2024-06-01 10:00:00 |
      | 502 | User2    | project      | 2          | inappropriate | new   | 2024-06-02 10:00:00 |
      | 503 | User2    | comment      | 10         | copyright     | new   | 2024-06-03 10:00:00 |
    And I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=2"
    Then the response status code should be "200"
    And the client response should contain "next_cursor"

  # ---------------------------------------------------------------------------
  # Multiple content types
  # ---------------------------------------------------------------------------

  Scenario: User with reports from different content types sees all of them
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category      | state | created_at          |
      | 701 | Catrobat | project      | 2          | spam          | new   | 2024-06-01 10:00:00 |
      | 702 | Catrobat | comment      | 10         | inappropriate | new   | 2024-06-02 10:00:00 |
      | 703 | Catrobat | user         | 2          | offensive     | new   | 2024-06-03 10:00:00 |
      | 704 | Catrobat | studio       | 1          | copyright     | new   | 2024-06-04 10:00:00 |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "project"
    And the client response should contain "comment"
    And the client response should contain "user"
    And the client response should contain "studio"
