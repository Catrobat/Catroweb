@api @achievements
Feature: Achievements API endpoints

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And there are achievements:
      | id | internal_title     | title_ltm_code | description_ltm_code | priority |
      | 1  | best_user          | best__         | best__desc           | 2        |
      | 2  | first_achiever     | first__        | first__desc          | 3        |
      | 3  | master_of_disaster | ups__          | ups__desc            | 1        |
    And there are user achievements:
      | id | user     | achievement        | seen_at    | unlocked_at |
      | 1  | Catrobat | best_user          | 2021-05-05 | 2021-05-05  |
      | 2  | Catrobat | master_of_disaster |            | 2021-03-03  |

  # ---------------------------------------------------------------------------
  # GET /api/achievements — authentication
  # ---------------------------------------------------------------------------

  Scenario: Unauthenticated request returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements"
    Then the response status code should be "401"

  Scenario: Authenticated request returns 200 with achievement data
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "unlocked"
    And the client response should contain "locked"
    And the client response should contain "most_recent"
    And the client response should contain "show_animation"
    And the client response should contain "total_count"
    And the client response should contain "unlocked_count"

  Scenario: Achievements list contains correct unlocked and locked counts
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements"
    Then the response status code should be "200"
    And the client response should contain "total_count"
    And the client response should contain "unlocked_count"

  Scenario: Show animation is true when most recent achievement is unseen
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements"
    Then the response status code should be "200"
    And the client response should contain "show_animation"

  Scenario: Show animation is false when all achievements are seen
    Given there are user achievements:
      | id | user  | achievement        | seen_at    | unlocked_at |
      | 1  | User2 | best_user          | 2021-05-05 | 2021-05-05  |
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements"
    Then the response status code should be "200"
    And the client response should contain "show_animation"

  # ---------------------------------------------------------------------------
  # GET /api/achievements/count
  # ---------------------------------------------------------------------------

  Scenario: Unauthenticated count request returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements/count"
    Then the response status code should be "401"

  Scenario: Count returns number of unseen achievements
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements/count"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "count"

  Scenario: Count returns 0 when all achievements are seen
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements/count"
    Then the response status code should be "200"
    And the client response should contain "count"

  # ---------------------------------------------------------------------------
  # PUT /api/achievements/read
  # ---------------------------------------------------------------------------

  Scenario: Unauthenticated read request returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "PUT" "/api/achievements/read"
    Then the response status code should be "401"

  Scenario: Mark all achievements as read returns 204
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "PUT" "/api/achievements/read"
    Then the response status code should be "204"

  Scenario: After marking as read, count returns 0
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "PUT" "/api/achievements/read"
    Then the response status code should be "204"
    When I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/achievements/count"
    Then the response status code should be "200"
    And the client response should contain "count"
