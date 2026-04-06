@api @studio
Feature: Join a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | User1  |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user  | studio_name | role  |
      | 1  | Admin | Studio1     | admin |

  Scenario: Unauthenticated user cannot join
    When I POST "/api/studio/1/join"
    Then the response status code should be "401"

  Scenario: Authenticated user joins a public studio
    Given I use a valid JWT Bearer token for "User1"
    When I POST "/api/studio/1/join"
    Then the response status code should be "200"

  Scenario: Already a member gets conflict
    Given I use a valid JWT Bearer token for "Admin"
    When I POST "/api/studio/1/join"
    Then the response status code should be "409"

  Scenario: Join non-existent studio returns 404
    Given I use a valid JWT Bearer token for "User1"
    When I POST "/api/studio/nonexistent/join"
    Then the response status code should be "404"

  Scenario: Join private studio creates pending request
    Given there are studios:
      | id | name           | description    | is_public |
      | 2  | Private Studio | Private studio | false     |
    And there are studio users:
      | id | user  | studio_name    | role  |
      | 3  | Admin | Private Studio | admin |
    Given I use a valid JWT Bearer token for "User1"
    When I POST "/api/studio/2/join"
    Then the response status code should be "200"
