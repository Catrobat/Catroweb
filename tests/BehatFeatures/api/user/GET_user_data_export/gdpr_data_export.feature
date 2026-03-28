@api @user
Feature: GDPR personal data export

  Background:
    Given there are users:
      | name     | password | id |
      | Catrobat | 12345    | 1  |
      | User1    | vwxyz    | 2  |
    And there are followers:
      | name     | following |
      | User1    | Catrobat  |
    And there are projects:
      | id        | name       | owned by | version | private | visible |
      | isxs-adkt | MyProject  | Catrobat | 0.8.5   | false   | true    |
    And there are comments:
      | id | user     | project_id | text          |
      | 10 | Catrobat | isxs-adkt  | First comment |
    And I wait 500 milliseconds

  Scenario: Successfully export personal data
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/data-export"
    Then the response status code should be "200"
    And the client response should contain "exported_at"
    And the client response should contain "profile"
    And the client response should contain "Catrobat"
    And the client response should contain "projects"
    And the client response should contain "MyProject"
    And the client response should contain "comments"
    And the client response should contain "First comment"
    And the client response should contain "followers"
    And the client response should contain "User1"
    And the client response should contain "reactions"
    And the client response should contain "following"
    And the client response should contain "created_at"

  Scenario: Export data requires authentication
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/data-export"
    Then the response status code should be "401"

  Scenario: Export data with invalid token returns 401
    Given I use an invalid JWT Bearer token
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/user/data-export"
    Then the response status code should be "401"
