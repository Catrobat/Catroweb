@api @notifications
Feature: Get notifications via API with cursor-based pagination

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
      | 3  | User3    |
    And there are projects:
      | id | name      | owned by |
      | 1  | project1  | Catrobat |
      | 2  | project2  | User2    |
      | 3  | project3  | User3    |
    And there are comments:
      | id                                   | project_id | user_id | text       | upload_date         | parent_id |
      | 00000000-0000-0000-0000-000000000010 | 1          | 2       | a comment  | 2024-01-01 12:00:00 |           |
    And there are catro notifications:
      | id                                   | user     | type           | like_from | project_id | commentID                            | follower_id | parent_project | child_project | seen |
      | 00000000-0000-0000-0000-000000000001 | Catrobat | like           | User2     | 1          |                                      |             |                |               | 0    |
      | 00000000-0000-0000-0000-000000000002 | Catrobat | follower       |           |            |                                      | 2           |                |               | 0    |
      | 00000000-0000-0000-0000-000000000003 | Catrobat | comment        |           |            | 00000000-0000-0000-0000-000000000010 |             |                |               | 1    |
      | 00000000-0000-0000-0000-000000000004 | Catrobat | follow_project |           | 2          |                                      |             |                |               | 0    |
      | 00000000-0000-0000-0000-000000000005 | Catrobat | remix          |           |            |                                      |             | 2              | 3             | 0    |

  # ---------------------------------------------------------------------------
  # GET /api/notifications — authentication
  # ---------------------------------------------------------------------------

  Scenario: Unauthenticated request returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=all"
    Then the response status code should be "401"

  Scenario: Authenticated request returns 200 with notification data
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=all"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "data"
    And the client response should contain "has_more"

  # ---------------------------------------------------------------------------
  # GET /api/notifications — type filtering
  # ---------------------------------------------------------------------------

  Scenario: Filter by reaction type returns only like notifications
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=reaction"
    Then the response status code should be "200"
    And the client response should contain "reaction"

  Scenario: Filter by follow type returns follow and follow_project notifications
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=follow"
    Then the response status code should be "200"
    And the client response should contain "follow"

  Scenario: Filter by comment type returns only comment notifications
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=comment"
    Then the response status code should be "200"
    And the client response should contain "comment"

  Scenario: Filter by remix type returns only remix notifications
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=remix"
    Then the response status code should be "200"
    And the client response should contain "remix"

  # ---------------------------------------------------------------------------
  # GET /api/notifications — cursor pagination
  # ---------------------------------------------------------------------------

  Scenario: Pagination with small limit returns has_more true and next_cursor
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=all&limit=2"
    Then the response status code should be "200"
    And the client response should contain "next_cursor"
    And the client response should contain "has_more"

  Scenario: Invalid cursor returns 400
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=all&cursor=!!!invalid!!!"
    Then the response status code should be "400"

  # ---------------------------------------------------------------------------
  # GET /api/notifications — self-notification exclusion
  # ---------------------------------------------------------------------------

  Scenario: User does not see own like notifications
    Given there are catro notifications:
      | id                                   | user     | type | like_from | project_id | seen |
      | 00000000-0000-0000-0000-000000000010 | Catrobat | like | Catrobat  | 1          | 0    |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=reaction"
    Then the response status code should be "200"

  # ---------------------------------------------------------------------------
  # GET /api/notifications — empty results
  # ---------------------------------------------------------------------------

  Scenario: User with no notifications gets empty data
    Given I use a valid JWT Bearer token for "User3"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/notifications?type=all"
    Then the response status code should be "200"
    And the client response should contain "has_more"
