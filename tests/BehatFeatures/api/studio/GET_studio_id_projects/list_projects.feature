@api @studio
Feature: List studio projects

  Background:
    Given there are users:
      | id | name       |
      | 1  | Admin      |
      | 2  | Non-member |
    And there are projects:
      | id | name     | owned by |
      | 1  | Project1 | Admin    |
      | 2  | Project2 | Admin    |
    And there are studios:
      | id | name           | description     | is_public |
      | 1  | Studio1        | Test studio     | true      |
      | 2  | PrivateStudio  | Private studio  | false     |
    And there are studio users:
      | id | user  | studio_name   | role  |
      | 1  | Admin | Studio1       | admin |
      | 2  | Admin | PrivateStudio | admin |
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

  Scenario: List projects with limit
    When I GET "/api/studio/1/projects?limit=1"
    Then the response status code should be "200"
    And the client response should contain "has_more"

  Scenario: Response contains project details
    When I GET "/api/studio/1/projects"
    Then the response status code should be "200"
    And the client response should contain "added_by"
    And the client response should contain "added_at"

  Scenario: Invalid cursor returns 400
    When I GET "/api/studio/1/projects?cursor=invalid!!"
    Then the response status code should be "400"

  Scenario: Non-member cannot list projects of a private studio
    Given I use a valid JWT Bearer token for "Non-member"
    When I GET "/api/studio/2/projects"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot list projects of a private studio
    When I GET "/api/studio/2/projects"
    Then the response status code should be "403"

  Scenario: Member of private studio can list projects
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studio/2/projects"
    Then the response status code should be "200"
