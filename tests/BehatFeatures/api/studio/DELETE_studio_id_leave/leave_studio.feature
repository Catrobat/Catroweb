@api @studio
Feature: Leave a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |

  Scenario: Unauthenticated user cannot leave
    When I DELETE "/api/studios/1/leave"
    Then the response status code should be "401"

  Scenario: Member leaves studio
    Given I use a valid JWT Bearer token for "Member"
    When I DELETE "/api/studios/1/leave"
    Then the response status code should be "204"

  Scenario: Admin cannot leave studio
    Given I use a valid JWT Bearer token for "Admin"
    When I DELETE "/api/studios/1/leave"
    Then the response status code should be "422"

  Scenario: Non-member trying to leave returns 404
    Given there are users:
      | id | name     |
      | 3  | Outsider |
    Given I use a valid JWT Bearer token for "Outsider"
    When I DELETE "/api/studios/1/leave"
    Then the response status code should be "404"

  Scenario: Leave non-existent studio returns 404
    Given I use a valid JWT Bearer token for "Member"
    When I DELETE "/api/studios/nonexistent/leave"
    Then the response status code should be "404"
