@api @studio
Feature: Studio join request management

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | User1  |
      | 3  | User2  |
      | 4  | Member |
    And there are studios:
      | id | name           | description    | is_public |
      | 1  | Private Studio | Private studio | false     |
    And there are studio users:
      | id | user   | studio_name    | role   |
      | 1  | Admin  | Private Studio | admin  |
      | 2  | Member | Private Studio | member |
    And there are studio join requests:
      | id                                   | User  | Studio         | Status  |
      | 00000000-0000-0000-0000-000000000101 | User1 | Private Studio | pending |
      | 00000000-0000-0000-0000-000000000102 | User2 | Private Studio | pending |

  Scenario: Unauthenticated user cannot list join requests
    When I GET "/api/studios/1/join-requests"
    Then the response status code should be "401"

  Scenario: Non-admin member cannot list join requests
    Given I use a valid JWT Bearer token for "Member"
    When I GET "/api/studios/1/join-requests"
    Then the response status code should be "403"

  Scenario: Admin can list pending join requests
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/1/join-requests"
    Then the response status code should be "200"
    And the client response should contain "User1"
    And the client response should contain "User2"

  Scenario: Non-existent studio returns 404
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/nonexistent/join-requests"
    Then the response status code should be "404"

  Scenario: Admin can accept a join request
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/accept"
    Then the response status code should be "200"
    When I GET "/api/studios/1/join-requests"
    Then the response status code should be "200"
    And the client response should not contain "User1"
    And the client response should contain "User2"

  Scenario: Admin can decline a join request
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/decline"
    Then the response status code should be "200"
    When I GET "/api/studios/1/join-requests"
    Then the response status code should be "200"
    And the client response should not contain "User1"
    And the client response should contain "User2"

  Scenario: Unauthenticated user cannot accept a join request
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/accept"
    Then the response status code should be "401"

  Scenario: Non-admin cannot accept a join request
    Given I use a valid JWT Bearer token for "Member"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/accept"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot decline a join request
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/decline"
    Then the response status code should be "401"

  Scenario: Non-admin cannot decline a join request
    Given I use a valid JWT Bearer token for "Member"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/decline"
    Then the response status code should be "403"

  Scenario: Accept non-existent join request returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000009999/accept"
    Then the response status code should be "404"

  Scenario: Decline non-existent join request returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000009999/decline"
    Then the response status code should be "404"

  Scenario: Cannot accept an already declined join request
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/decline"
    Then the response status code should be "200"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/accept"
    Then the response status code should be "422"

  Scenario: Accepting a join request creates a notification for the requesting user
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/accept"
    Then the response status code should be "200"
    When I use a valid JWT Bearer token for "User1"
    And I request "GET" "/api/notifications?type=studio"
    Then the response status code should be "200"
    And the client response should contain "accepted"

  Scenario: Declining a join request creates a notification for the requesting user
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    When I POST "/api/studios/1/join-requests/00000000-0000-0000-0000-000000000101/decline"
    Then the response status code should be "200"
    When I use a valid JWT Bearer token for "User1"
    And I request "GET" "/api/notifications?type=studio"
    Then the response status code should be "200"
    And the client response should contain "declined"
