@api @search
Feature: Search hides auto-hidden projects and the projects of banned users

  Background:
    Given there are users:
      | id | name     |
      | 1  | Uploader |
      | 2  | Reporter |
      | 3  | Other    |
    And the users are created at:
      | name     | created_at          |
      | Reporter | 2024-01-01 12:00:00 |
    And there are admins:
      | name  |
      | Admin |
    And there are projects:
      | id | name          | owned by | auto_hidden |
      | 1  | RocketHidden  | Other    | true        |
      | 2  | RocketVisible | Other    | false       |
      | 3  | RocketBad     | Uploader | false       |
      | 4  | RocketSibling | Uploader | false       |
    And I wait for the search index to be updated

  Scenario: Auto-hidden projects are not found by the combined search
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Rocket&type=all"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name          |
      | RocketVisible |
      | RocketBad     |
      | RocketSibling |
    And the client response should not contain "RocketHidden"

  Scenario: Auto-hidden projects are not found by the project search
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search?query=Rocket"
    Then the response status code should be "200"
    And the client response should contain "RocketVisible"
    And the client response should not contain "RocketHidden"

  Scenario: Banning a user hides all their projects from search, unbanning brings back the unreported ones
    # Reporting RocketBad and accepting the report bans its owner, which hides all of the owner's projects
    Given the next Uuid Value will be "00000000-0000-0000-0000-000000000001"
    And I use a valid JWT Bearer token for "Reporter"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/3/report"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "accept"}
      """
    When I request "PUT" "/api/moderation/reports/00000000-0000-0000-0000-000000000001/resolve"
    Then the response status code should be "200"
    Given I wait for the search index to be updated
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/search?query=Rocket&type=projects"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name          |
      | RocketVisible |
    And the client response should not contain "RocketBad"
    And the client response should not contain "RocketSibling"
    # An approved appeal unbans the owner; only the project without an accepted report comes back
    Given the next Uuid Value will be "00000000-0000-0000-0000-000000000002"
    And I use a valid JWT Bearer token for "Uploader"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Wrongly banned"}
      """
    When I request "POST" "/api/users/1/appeal"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "approve", "note": "Restored"}
      """
    When I request "PUT" "/api/moderation/appeals/00000000-0000-0000-0000-000000000002/resolve"
    Then the response status code should be "200"
    Given I wait for the search index to be updated
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/search?query=Rocket&type=projects"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name          |
      | RocketVisible |
      | RocketSibling |
    And the client response should not contain "RocketBad"
