@api @user
Feature: User leaderboard

  GET /api/users?order_by=rank|rank_recent returns the top creators, scored nightly by
  catrobat:update:userranking. Suspended users and users awaiting parental consent are left out.

  Background:
    Given there are users:
      | id | name  | consent_status |
      | 1  | Alice | not_required   |
      | 2  | Bob   | not_required   |
      | 3  | Carol | not_required   |
      | 4  | Dave  | not_required   |
      | 5  | Erin  | pending        |
      | 6  | Frank | not_required   |
    And there are projects:
      | id | name        | owned by | downloads | private |
      | 1  | Alice one   | Alice    | 100       | false   |
      | 2  | Alice two   | Alice    | 100       | false   |
      | 3  | Bob one     | Bob      | 300       | false   |
      | 4  | Carol one   | Carol    | 0         | false   |
      | 5  | Carol hid   | Carol    | 1000      | true    |
      | 6  | Dave one    | Dave     | 50        | false   |
      | 7  | Erin one    | Erin     | 50        | false   |
    And there are project downloads:
      | project   | downloaded by | days ago |
      | Alice one | Bob           | 1        |
      | Alice one | Carol         | 2        |
      | Alice two | Bob           | 10       |
      | Bob one   | Bob           | 1        |
      | Bob one   | Alice         | 3        |
      | Dave one  | Alice         | 1        |
    And the user "Dave" profile is hidden
    And I run the update user ranking command
    And I have a request header "HTTP_ACCEPT" with value "application/json"

  Scenario: All-time leaderboard is ordered by the smoothed score and skips hidden, pending and unranked users
    Given I have a parameter "order_by" with value "rank"
    When I request "GET" "/api/users"
    Then the response status code should be "200"
    And the client response should contain "13333"
    And the client response should contain "10000"
    And the client response should contain "Bob" before "Alice"
    And the client response should not contain "Carol"
    And the client response should not contain "Dave"
    And the client response should not contain "Erin"
    And the client response should not contain "Frank"
    And the client response should not contain "ranking_score_recent"

  Scenario: Weekly leaderboard only counts downloads by others in the last 7 days
    Given I have a parameter "order_by" with value "rank_recent"
    When I request "GET" "/api/users"
    Then the response status code should be "200"
    And the client response should contain "144"
    And the client response should contain "128"
    And the client response should contain "Alice" before "Bob"
    And the client response should not contain "Dave"

  Scenario: The leaderboard is paginated with a cursor
    Given I have a parameter "order_by" with value "rank"
    And I have a parameter "limit" with value "1"
    When I request "GET" "/api/users"
    Then the response status code should be "200"
    And the client response should contain "Bob"
    And the client response should not contain "Alice"
    And the client response should contain "MQ=="

  Scenario: The second page continues after the cursor
    Given I have a parameter "order_by" with value "rank"
    And I have a parameter "limit" with value "1"
    And I have a parameter "cursor" with value "MQ=="
    When I request "GET" "/api/users"
    Then the response status code should be "200"
    And the client response should contain "Alice"
    And the client response should not contain "Bob"
    And the client response should not contain "MQ=="

  Scenario: Rerunning the command gives the same scores
    Given I run the update user ranking command
    And I have a parameter "order_by" with value "rank"
    When I request "GET" "/api/users"
    Then the response status code should be "200"
    And the client response should contain "13333"
    And the client response should contain "10000"

  Scenario: An unknown order is rejected
    Given I have a parameter "order_by" with value "elo"
    When I request "GET" "/api/users"
    Then the response status code should be "400"

  Scenario: order_by cannot be combined with a search query
    Given I have a parameter "order_by" with value "rank"
    And I have a parameter "query" with value "Bob"
    When I request "GET" "/api/users"
    Then the response status code should be "400"

  Scenario: The default listing still requires authentication
    When I request "GET" "/api/users"
    Then the response status code should be "401"
