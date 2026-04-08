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
      | User  | Studio         | Status  |
      | User1 | Private Studio | pending |
      | User2 | Private Studio | pending |

  Scenario: Unauthenticated user cannot list join requests
    When I GET "/api/studio/1/join-requests"
    Then the response status code should be "401"

  Scenario: Non-admin member cannot list join requests
    Given I use a valid JWT Bearer token for "Member"
    When I GET "/api/studio/1/join-requests"
    Then the response status code should be "403"

  Scenario: Admin can list pending join requests
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studio/1/join-requests"
    Then the response status code should be "200"
    And the client response should contain "User1"
    And the client response should contain "User2"

  Scenario: Non-existent studio returns 404
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studio/nonexistent/join-requests"
    Then the response status code should be "404"

  Scenario: Admin can accept a join request
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studio/1/join-requests"
    Then the response status code should be "200"
    And the client response should contain "User1"

  Scenario: Unauthenticated user cannot accept a join request
    When I POST "/api/studio/1/join-requests/1/accept"
    Then the response status code should be "401"

  Scenario: Non-admin cannot accept a join request
    Given I use a valid JWT Bearer token for "Member"
    When I POST "/api/studio/1/join-requests/1/accept"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot decline a join request
    When I POST "/api/studio/1/join-requests/1/decline"
    Then the response status code should be "401"

  Scenario: Non-admin cannot decline a join request
    Given I use a valid JWT Bearer token for "Member"
    When I POST "/api/studio/1/join-requests/1/decline"
    Then the response status code should be "403"

  Scenario: Accept non-existent join request returns 404
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studio/1/join-requests/999/accept"
    Then the response status code should be "404"

  Scenario: Decline non-existent join request returns 404
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studio/1/join-requests/999/decline"
    Then the response status code should be "404"
