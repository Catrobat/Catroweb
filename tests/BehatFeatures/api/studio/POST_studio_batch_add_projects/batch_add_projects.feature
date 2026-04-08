@api @studio
Feature: Batch add projects to a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
      | 3  | Guest  |
    And there are projects:
      | id | name     | owned by |
      | 1  | Project1 | Admin    |
      | 2  | Project2 | Admin    |
      | 3  | Project3 | Member   |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |

  Scenario: Successfully batch add multiple projects
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "2", "3"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "200"
    And the client response should contain "added"
    And the client response should contain "added"

  Scenario: Partial success with non-existent projects
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "nonexistent"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "200"
    And the client response should contain "added"
    And the client response should contain "failed"
    And the client response should contain "not_found"

  Scenario: Duplicate projects already in studio
    Given there are studio projects:
      | user  | studio_name | project_name |
      | Admin | Studio1     | Project1     |
    And I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "2"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "200"
    And the client response should contain "added"
    And the client response should contain "failed"
    And the client response should contain "conflict"

  Scenario: All projects already in studio
    Given there are studio projects:
      | user   | studio_name | project_name |
      | Admin  | Studio1     | Project1     |
      | Member | Studio1     | Project2     |
    And I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "2"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "200"
    And the client response should contain "conflict"

  Scenario: Unauthenticated user cannot batch add projects
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "2"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "401"

  Scenario: Non-member cannot batch add projects
    Given I use a valid JWT Bearer token for "Guest"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "2"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "403"

  Scenario: Empty project_ids array returns 400
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": []}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "400"

  Scenario: Non-existent studio returns 404
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1"]}
      """
    When I POST "/api/studio/nonexistent/batch-add-projects"
    Then the response status code should be "404"

  Scenario: Mixed results with valid, duplicate, and non-existent projects
    Given there are studio projects:
      | user  | studio_name | project_name |
      | Admin | Studio1     | Project1     |
    And I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "2", "nonexistent"]}
      """
    When I POST "/api/studio/1/batch-add-projects"
    Then the response status code should be "200"
    And the client response should contain "added"
    And the client response should contain "failed"
    And the client response should contain "conflict"
    And the client response should contain "not_found"
