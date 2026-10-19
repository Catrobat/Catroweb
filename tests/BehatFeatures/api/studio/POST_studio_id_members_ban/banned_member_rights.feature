@api @studio
Feature: A studio ban takes away every membership right

  Background:
    Given there are users:
      | id | name         | password |
      | 1  | StudioAdmin  | 123456   |
      | 2  | BannedMember | 123456   |
      | 3  | BannedAdmin  | 123456   |
      | 4  | Newcomer     | 123456   |
    And there are projects:
      | id | name          | owned by     |
      | 1  | BannedProject | BannedMember |
    And there are studios:
      | id | name           | description | is_public | allow_comments |
      | 1  | PublicStudio   | Public      | true      | true           |
      | 2  | PrivateStudio  | Private     | false     | true           |
      | 3  | FirstAdminGone | Public      | true      | true           |
    And there are studio users:
      | id | user         | studio_id | role   | status |
      | 1  | StudioAdmin  | 1         | admin  | active |
      | 2  | BannedMember | 1         | member | banned |
      | 3  | BannedAdmin  | 1         | admin  | banned |
      | 4  | StudioAdmin  | 2         | admin  | active |
      | 5  | BannedMember | 2         | member | banned |
      | 6  | BannedAdmin  | 3         | admin  | banned |
      | 7  | StudioAdmin  | 3         | admin  | active |

  Scenario: A banned member cannot add a project
    Given I use a valid JWT Bearer token for "BannedMember"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "1"}
      """
    When I POST "/api/studios/1/projects"
    Then the response status code should be "403"

  Scenario: A banned member cannot comment
    Given I use a valid JWT Bearer token for "BannedMember"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "still here"}
      """
    When I POST "/api/studios/1/comments"
    Then the response status code should be "403"

  Scenario Outline: A banned member cannot read a private studio
    Given I use a valid JWT Bearer token for "BannedMember"
    When I request "GET" "/api/studios/2/<list>"
    Then the response status code should be "403"

    Examples:
      | list     |
      | projects |
      | comments |
      | members  |

  Scenario: A banned member cannot re-join
    Given I use a valid JWT Bearer token for "BannedMember"
    When I request "POST" "/api/studios/1/join"
    Then the response status code should be "403"

  Scenario: A banned member cannot lift the ban by leaving
    Given I use a valid JWT Bearer token for "BannedMember"
    When I request "DELETE" "/api/studios/1/leave"
    Then the response status code should be "404"
    When I request "POST" "/api/studios/1/join"
    Then the response status code should be "403"

  Scenario: A banned admin cannot edit the studio
    Given I use a valid JWT Bearer token for "BannedAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value          |
      | description | Taken over now |
    When I request "PATCH" "/api/studios/1"
    Then the response status code should be "403"

  Scenario: A banned admin cannot delete the studio
    Given I use a valid JWT Bearer token for "BannedAdmin"
    When I request "DELETE" "/api/studios/1"
    Then the response status code should be "403"

  Scenario: A banned admin cannot ban the remaining admin
    Given I use a valid JWT Bearer token for "BannedAdmin"
    When I request "POST" "/api/studios/1/members/1/ban"
    Then the response status code should be "403"

  Scenario: A banned admin cannot read the activity log
    Given I use a valid JWT Bearer token for "BannedAdmin"
    When I request "GET" "/api/studios/1/activities"
    Then the response status code should be "403"

  Scenario: A banned admin is not shown admin data
    Given I use a valid JWT Bearer token for "BannedAdmin"
    When I request "GET" "/api/studios/1"
    Then the response status code should be "200"
    And the client response should not contain "pending_join_requests_count"

  Scenario: Joining still works when the studio's first admin is banned
    Given I use a valid JWT Bearer token for "Newcomer"
    When I request "POST" "/api/studios/3/join"
    Then the response status code should be "200"
    When I request "GET" "/api/studios/3/members"
    Then the client response should contain "Newcomer"
