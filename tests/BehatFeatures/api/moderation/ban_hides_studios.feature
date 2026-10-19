@api @moderation
Feature: Banning a user hides the studios they own, unbanning brings them back

  Background:
    Given there are users:
      | id | name     |
      | 1  | Uploader |
      | 2  | Reporter |
      | 3  | CoAdmin  |
    And the users are created at:
      | name     | created_at          |
      | Reporter | 2024-01-01 12:00:00 |
    And there are admins:
      | name  |
      | Admin |
    And there are projects:
      | id | name      | owned by |
      | 1  | BadProj   | Uploader |
    And there are studios:
      | id                                   | name          | description | is_public |
      | 10000000-0000-0000-0000-000000000001 | GalaxyOwned   | owned       | true      |
      | 10000000-0000-0000-0000-000000000002 | GalaxyShared  | shared      | true      |
    And there are studio users:
      | user     | studio_name  | role  | created_on       |
      | Uploader | GalaxyOwned  | admin | 01.01.2020 12:00 |
      | CoAdmin  | GalaxyShared | admin | 01.01.2020 12:00 |
      | Uploader | GalaxyShared | admin | 01.01.2021 12:00 |
    And I wait for the search index to be updated

  Scenario: Ban hides only the studios the banned user owns; an approved appeal restores them
    # Reporting BadProj and accepting the report bans its owner
    Given the next Uuid Value will be "00000000-0000-0000-0000-000000000001"
    And I use a valid JWT Bearer token for "Reporter"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/projects/1/report"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "accept"}
      """
    When I request "PUT" "/api/moderation/reports/00000000-0000-0000-0000-000000000001/resolve"
    Then the response status code should be "200"
    And the studio "GalaxyOwned" should be hidden
    And the studio "GalaxyShared" should be visible
    Given I wait for the search index to be updated
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/search?query=Galaxy&type=studios"
    Then the response code should be "200"
    And the client response should contain "GalaxyShared"
    And the client response should not contain "GalaxyOwned"
    When I request "GET" "/api/studios/10000000-0000-0000-0000-000000000001"
    Then the response status code should be "404"
    # An approved appeal unbans the owner and brings the studio back
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
    And the studio "GalaxyOwned" should be visible
    Given I wait for the search index to be updated
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/search?query=Galaxy&type=studios"
    Then the response code should be "200"
    And the client response should contain "GalaxyOwned"
    When I request "GET" "/api/studios/10000000-0000-0000-0000-000000000001"
    Then the response status code should be "200"
