@api @studio
Feature: Studio admins manage invite codes

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
      | 3  | Guest  |
    And there are studios:
      | id | name    | description | is_public |
      | 1  | Class1  | A class     | false     |
      | 2  | Other   | Elsewhere   | false     |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Class1      | admin  |
      | 2  | Member | Class1      | member |
      | 3  | Guest  | Other       | admin  |
    And there are studio invites:
      | id                                   | studio_name | code       |
      | 00000000-0000-0000-0000-0000000000a1 | Class1      | CLASS1CODE |
      | 00000000-0000-0000-0000-0000000000a2 | Other       | OTHERCODE2 |

  Scenario: Unauthenticated users cannot create invites
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {}
      """
    When I POST "/api/studios/1/invites"
    Then the response status code should be "401"

  Scenario: Members who are not admins cannot create or list invites
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {}
      """
    When I POST "/api/studios/1/invites"
    Then the response status code should be "403"
    When I GET "/api/studios/1/invites"
    Then the response status code should be "403"

  Scenario: An admin creates an invite without limits
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {}
      """
    When I POST "/api/studios/1/invites"
    Then the response status code should be "201"
    And the client response should contain "uses"
    And the client response should not contain "max_uses"
    And the client response should contain "/studio/join/"

  Scenario: An admin creates an invite with expiry and max uses
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"expires_at": "2099-01-01T00:00:00+00:00", "max_uses": 30}
      """
    When I POST "/api/studios/1/invites"
    Then the response status code should be "201"
    And the client response should contain "max_uses"
    And the client response should contain ":30"
    And the client response should contain "2099-01-01"

  Scenario: An expiry in the past is rejected
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"expires_at": "2001-01-01T00:00:00+00:00"}
      """
    When I POST "/api/studios/1/invites"
    Then the response status code should be "422"

  Scenario: An admin lists only the invites of their studio
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/1/invites"
    Then the response status code should be "200"
    And the client response should contain "CLASS1CODE"
    And the client response should not contain "OTHERCODE2"

  Scenario: An admin revokes an invite and the code stops working
    Given I use a valid JWT Bearer token for "Admin"
    When I DELETE "/api/studios/1/invites/00000000-0000-0000-0000-0000000000a1"
    Then the response status code should be "204"
    Given I use a valid JWT Bearer token for "Guest"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "CLASS1CODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "404"

  Scenario: An admin cannot revoke the invite of another studio
    Given I use a valid JWT Bearer token for "Admin"
    When I DELETE "/api/studios/1/invites/00000000-0000-0000-0000-0000000000a2"
    Then the response status code should be "404"
