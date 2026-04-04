@api @studio
Feature: Add a project to a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
      | 3  | Guest  |
    And there are projects:
      | id | name     | owned by |
      | 1  | Project1 | Admin    |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |

  Scenario: Unauthenticated user cannot add project
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "1"}
      """
    When I POST "/api/studio/1/projects"
    Then the response status code should be "401"

  Scenario: Non-member cannot add project
    Given I use a valid JWT Bearer token for "Guest"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "1"}
      """
    When I POST "/api/studio/1/projects"
    Then the response status code should be "403"

  Scenario: Member adds project successfully
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "1"}
      """
    When I POST "/api/studio/1/projects"
    Then the response status code should be "201"

  Scenario: Add project to non-existent studio returns 404
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "1"}
      """
    When I POST "/api/studio/nonexistent/projects"
    Then the response status code should be "404"

  Scenario: Add non-existent project returns 404
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "nonexistent"}
      """
    When I POST "/api/studio/1/projects"
    Then the response status code should be "404"

  Scenario: Add duplicate project returns 409
    Given there are studio projects:
      | user  | studio_name | project_name |
      | Admin | Studio1     | Project1     |
    And I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "1"}
      """
    When I POST "/api/studio/1/projects"
    Then the response status code should be "409"

  Scenario: Add project with empty project_id returns 400
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": ""}
      """
    When I POST "/api/studio/1/projects"
    Then the response status code should be "400"
