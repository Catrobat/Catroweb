@api @studio
Feature: Post a comment to a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
      | 3  | Guest  |
    And there are studios:
      | id | name    | description  | is_public | allow_comments |
      | 1  | Studio1 | Test studio  | true      | true           |
      | 2  | Studio2 | No comments  | true      | false          |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |
      | 3  | Admin  | Studio2     | admin  |

  Scenario: Unauthenticated user cannot post comment
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello studio"}
      """
    When I POST "/api/studio/1/comments"
    Then the response status code should be "401"

  Scenario: Non-member cannot post comment
    Given I use a valid JWT Bearer token for "Guest"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello studio"}
      """
    When I POST "/api/studio/1/comments"
    Then the response status code should be "403"

  Scenario: Member posts comment successfully
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello studio!"}
      """
    When I POST "/api/studio/1/comments"
    Then the response status code should be "201"
    And the client response should contain "Hello studio!"

  Scenario: Cannot post comment when comments are disabled
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Should fail"}
      """
    When I POST "/api/studio/2/comments"
    Then the response status code should be "403"

  Scenario: Empty message returns 400
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "   "}
      """
    When I POST "/api/studio/1/comments"
    Then the response status code should be "400"

  Scenario: Minor user cannot post comment
    Given there are users:
      | id | name      | is_minor |
      | 4  | MinorUser | true     |
    And there are studio users:
      | id | user      | studio_name | role   |
      | 4  | MinorUser | Studio1     | member |
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello from minor"}
      """
    When I POST "/api/studio/1/comments"
    Then the response status code should be "403"

  Scenario: Post comment to non-existent studio returns 404
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "Hello"}
      """
    When I POST "/api/studio/nonexistent/comments"
    Then the response status code should be "404"

  Scenario: Post comment with parent_id for reply
    Given there are studio comments:
      | user  | studio_name | comment        |
      | Admin | Studio1     | Parent comment |
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"message": "This is a reply", "parent_id": 1}
      """
    When I POST "/api/studio/1/comments"
    Then the response status code should be "201"
    And the client response should contain "This is a reply"
