@api @studio
Feature: Remove a project from a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
      | 3  | Guest  |
    And there are projects:
      | id | name     | owned by |
      | 1  | Project1 | Member   |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |
    And there are studio projects:
      | user   | studio_name | project_name |
      | Member | Studio1     | Project1     |

  Scenario: Unauthenticated user cannot remove project
    When I DELETE "/api/studio/1/projects/1"
    Then the response status code should be "401"

  Scenario: Non-member cannot remove project
    Given I use a valid JWT Bearer token for "Guest"
    When I DELETE "/api/studio/1/projects/1"
    Then the response status code should be "403"

  Scenario: Admin removes project
    Given I use a valid JWT Bearer token for "Admin"
    When I DELETE "/api/studio/1/projects/1"
    Then the response status code should be "204"
