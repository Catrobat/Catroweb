@api @studio
Feature: Report a studio comment

  Background:
    Given there are users:
      | id | name     | verified |
      | 1  | Admin    | true     |
      | 2  | Member   | true     |
      | 3  | Reporter | true     |
      | 4  | NewUser  | false    |
    And the users are created at:
      | name     | created_at          |
      | Admin    | 2024-01-01 12:00:00 |
      | Member   | 2024-01-01 12:00:00 |
      | Reporter | 2024-01-01 12:00:00 |
    And there are studios:
      | id | name    | description | is_public |
      | 1  | Studio1 | Test studio | true      |
    And there are studio users:
      | id | user     | studio_name | role   |
      | 1  | Admin    | Studio1     | admin  |
      | 2  | Member   | Studio1     | member |
      | 3  | Reporter | Studio1     | member |
    And there are studio comments:
      | id                                   | user   | studio_name | comment            |
      | 00000000-0000-0000-0000-000000000050 | Member | Studio1     | Inappropriate text |
      | 00000000-0000-0000-0000-000000000051 | Admin  | Studio1     | Admin comment      |

  Scenario: Report a studio comment (authenticated)
    Given I use a valid JWT Bearer token for "Reporter"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000050/report"
    Then the response status code should be "201"

  Scenario: Report a studio comment requires authentication
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000050/report"
    Then the response status code should be "401"

  Scenario: Report own studio comment returns 403
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000050/report"
    Then the response status code should be "403"

  Scenario: Duplicate report on studio comment returns 409
    Given I use a valid JWT Bearer token for "Reporter"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000050/report"
    Then the response status code should be "201"
    Given I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000050/report"
    Then the response status code should be "409"

  Scenario: Report studio comment with unverified email returns 403
    Given I use a valid JWT Bearer token for "NewUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000000050/report"
    Then the response status code should be "403"

  Scenario: Report non-existent studio comment returns 404
    Given I use a valid JWT Bearer token for "Reporter"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "inappropriate"}
      """
    When I request "POST" "/api/comments/00000000-0000-0000-0000-000000009999/report"
    Then the response status code should be "404"

  Scenario: Studio comment response includes user_approved field
    When I GET "/api/studios/1/comments"
    Then the response status code should be "200"
    And the client response should contain "user_approved"
