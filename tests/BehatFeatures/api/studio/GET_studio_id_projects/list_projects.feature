@api @studio
Feature: List studio projects

  Background:
    Given there are users:
      | id | name  |
      | 1  | Admin |
    And there are projects:
      | id | name     | owned by |
      | 1  | Project1 | Admin    |
      | 2  | Project2 | Admin    |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user  | studio_name | role  |
      | 1  | Admin | Studio1     | admin |
    And there are studio projects:
      | user  | studio_name | project_name |
      | Admin | Studio1     | Project1     |
      | Admin | Studio1     | Project2     |

  Scenario: List projects in a studio
    When I GET "/api/studio/1/projects"
    Then the response status code should be "200"
    And the client response should contain "Project1"
    And the client response should contain "Project2"
    And the client response should contain "has_more"

  Scenario: List projects of non-existent studio returns 404
    When I GET "/api/studio/nonexistent/projects"
    Then the response status code should be "404"
