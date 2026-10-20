@api @studio
Feature: Only active studio admins can remove a ban without restoring membership

  Background:
    Given there are users:
      | id | name        |
      | 1  | Admin       |
      | 2  | Member      |
      | 3  | Banned      |
      | 4  | BannedAdmin |
      | 5  | Outsider    |
    And there are studios:
      | id | name    | is_public |
      | 1  | Public  | true      |
      | 2  | Private | false     |
    And there are studio users:
      | id | user        | studio_id | role   | status |
      | 1  | Admin       | 1         | admin  | active |
      | 2  | Member      | 1         | member | active |
      | 3  | Banned      | 1         | member | banned |
      | 4  | BannedAdmin | 1         | admin  | banned |
      | 5  | Admin       | 2         | admin  | active |
      | 6  | Banned      | 2         | member | banned |

  Scenario: Guests cannot list bans or remove one
    When I GET "/api/studios/1/banned-members"
    Then the response status code should be "401"
    When I POST "/api/studios/1/members/3/unban"
    Then the response status code should be "401"

  Scenario Outline: Only active studio admins can list or remove bans
    Given I use a valid JWT Bearer token for "<viewer>"
    When I GET "/api/studios/1/banned-members"
    Then the response status code should be "403"
    When I POST "/api/studios/1/members/3/unban"
    Then the response status code should be "403"
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/1/banned-members"
    Then the JSON response field "data.0.username" should be '"Banned"'

    Examples:
      | viewer      |
      | Member      |
      | Banned      |
      | BannedAdmin |
      | Outsider    |

  Scenario: Admins can paginate the banned-member list without exposing active members
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/1/banned-members?limit=1"
    Then the response status code should be "200"
    And the JSON response field "data.0.username" should be '"Banned"'
    And the JSON response field "has_more" should be "true"
    When I GET "/api/studios/1/banned-members?limit=1&cursor=Mw=="
    Then the response status code should be "200"
    And the JSON response field "data.0.username" should be '"BannedAdmin"'
    And the JSON response field "has_more" should be "false"

  Scenario: Invalid ban-list cursors are rejected
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/1/banned-members?cursor=invalid!"
    Then the response status code should be "400"

  Scenario: Unbanning leaves a former member outside the studio until they rejoin
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studios/1/members/3/unban"
    Then the response status code should be "204"
    Given I use a valid JWT Bearer token for "Banned"
    When I GET "/api/studios/1"
    Then the JSON response field "is_banned" should be "false"
    And the JSON response field "is_member" should be "false"
    And the JSON response field "members_count" should be "2"
    When I POST "/api/studios/1/join"
    Then the response status code should be "200"
    When I GET "/api/studios/1"
    Then the JSON response field "is_member" should be "true"
    And the JSON response field "user_role" should be '"member"'

  Scenario: A former admin rejoins as a regular member
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studios/1/members/4/unban"
    Then the response status code should be "204"
    Given I use a valid JWT Bearer token for "BannedAdmin"
    When I POST "/api/studios/1/join"
    Then the response status code should be "200"
    When I GET "/api/studios/1"
    Then the JSON response field "user_role" should be '"member"'

  Scenario: An unbanned private-studio user still needs the normal join process
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studios/2/members/3/unban"
    Then the response status code should be "204"
    Given I use a valid JWT Bearer token for "Banned"
    When I GET "/api/studios/2/projects"
    Then the response status code should be "403"
    When I POST "/api/studios/2/join"
    Then the response status code should be "200"
    When I GET "/api/studios/2/projects"
    Then the response status code should be "403"

  Scenario Outline: An unban cannot remove an active membership or target a missing user
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studios/1/members/<target>/unban"
    Then the response status code should be "404"
    When I GET "/api/studios/1"
    Then the JSON response field "members_count" should be "2"

    Examples:
      | target      |
      | 2           |
      | 5           |
      | nonexistent |

  Scenario: An already removed ban returns not found
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studios/1/members/3/unban"
    Then the response status code should be "204"
    When I POST "/api/studios/1/members/3/unban"
    Then the response status code should be "404"
