@api @studio
Feature: The studio response tells a banned viewer that they are banned

  Background:
    Given there are users:
      | id | name         | password |
      | 1  | StudioAdmin  | 123456   |
      | 2  | BannedMember | 123456   |
      | 3  | Newcomer     | 123456   |
    And there are studios:
      | id | name         | description | is_public |
      | 1  | PublicStudio | Public      | true      |
    And there are studio users:
      | id | user         | studio_id | role   | status |
      | 1  | StudioAdmin  | 1         | admin  | active |
      | 2  | BannedMember | 1         | member | banned |

  Scenario: A banned member is flagged as banned and not as member
    Given I use a valid JWT Bearer token for "BannedMember"
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "is_banned" should be "true"
    And the JSON response field "is_member" should be "false"
    And the client response should not contain "user_role"

  Scenario: Other users are not flagged as banned
    Given I use a valid JWT Bearer token for "Newcomer"
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "is_banned" should be "false"

  Scenario: Active members are not flagged as banned
    Given I use a valid JWT Bearer token for "StudioAdmin"
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "is_banned" should be "false"
    And the client response should contain "user_role"

  Scenario: Guests get no ban flag
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the client response should not contain "is_banned"

  Scenario: The studio list flags the banned viewer too
    Given I use a valid JWT Bearer token for "BannedMember"
    When I GET "/api/studios"
    Then the response status code should be "200"
    And the JSON response field "data.0.is_banned" should be "true"
