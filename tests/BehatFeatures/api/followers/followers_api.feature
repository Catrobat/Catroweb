@api @followers
Feature: Followers API endpoints

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
      | 3  | User3    |
    And there are followers:
      | name     | following |
      | User2    | Catrobat  |
      | User3    | Catrobat  |
      | Catrobat | User2     |

  # ---------------------------------------------------------------------------
  # GET /api/users/{id}/followers — public endpoint
  # ---------------------------------------------------------------------------

  Scenario: Get followers without auth returns 200
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/1/followers"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "data"
    And the client response should contain "total_followers"
    And the client response should contain "total_following"

  Scenario: Get followers for non-existent user returns 404
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/nonexistent/followers"
    Then the response status code should be "404"

  Scenario: Get followers returns correct data structure
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/1/followers"
    Then the response status code should be "200"
    And the client response should contain "username"
    And the client response should contain "is_following"
    And the client response should contain "follows_you"

  # ---------------------------------------------------------------------------
  # GET /api/users/{id}/following — public endpoint
  # ---------------------------------------------------------------------------

  Scenario: Get following without auth returns 200
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/1/following"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "data"
    And the client response should contain "total_followers"
    And the client response should contain "total_following"

  Scenario: Get following for non-existent user returns 404
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/nonexistent/following"
    Then the response status code should be "404"

  # ---------------------------------------------------------------------------
  # POST /api/users/{id}/follow — requires auth
  # ---------------------------------------------------------------------------

  Scenario: Follow without auth returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/users/2/follow"
    Then the response status code should be "401"

  Scenario: Follow self returns 422
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/users/1/follow"
    Then the response status code should be "422"

  Scenario: Follow non-existent user returns 404
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/users/nonexistent/follow"
    Then the response status code should be "404"

  Scenario: Follow already-followed user returns 422
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/users/2/follow"
    Then the response status code should be "422"

  Scenario: Follow a new user returns 200
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/users/3/follow"
    Then the response status code should be "200"

  # ---------------------------------------------------------------------------
  # DELETE /api/users/{id}/unfollow — requires auth
  # ---------------------------------------------------------------------------

  Scenario: Unfollow without auth returns 401
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/users/2/unfollow"
    Then the response status code should be "401"

  Scenario: Unfollow self returns 422
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/users/1/unfollow"
    Then the response status code should be "422"

  Scenario: Unfollow non-existent user returns 404
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/users/nonexistent/unfollow"
    Then the response status code should be "404"

  Scenario: Unfollow a followed user returns 204
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "DELETE" "/api/users/2/unfollow"
    Then the response status code should be "204"
