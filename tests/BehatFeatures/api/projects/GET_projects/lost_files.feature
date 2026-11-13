@api @projects
Feature: Projects whose files were lost are hidden from everyone but their owner

  Background:
    Given there are users:
      | id | name     |
      | 1  | Owner    |
      | 2  | Stranger |
    And there are projects:
      | id | name         | owned by | has_missing_files |
      | 1  | RocketIntact | Owner    | false             |
      | 2  | RocketLost   | Owner    | true              |
    And I wait for the search index to be updated

  Scenario: Public project lists skip projects with lost files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/?category=recent"
    Then the response status code should be "200"
    And the client response should contain "RocketIntact"
    And the client response should not contain "RocketLost"

  Scenario: The owner's public profile list skips projects with lost files
    Given I use a valid JWT Bearer token for "Stranger"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/user/1/"
    Then the response status code should be "200"
    And the client response should contain "RocketIntact"
    And the client response should not contain "RocketLost"

  Scenario: Search skips projects with lost files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/search?query=Rocket"
    Then the response status code should be "200"
    And the client response should contain "RocketIntact"
    And the client response should not contain "RocketLost"

  Scenario: The owner still sees their project with lost files in their own list
    Given I use a valid JWT Bearer token for "Owner"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/user/"
    Then the response status code should be "200"
    And the client response should contain "RocketIntact"
    And the client response should contain "RocketLost"

  Scenario: Only the owner can open a project with lost files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/2"
    Then the response status code should be "404"
    Given I use a valid JWT Bearer token for "Stranger"
    When I request "GET" "/api/projects/2"
    Then the response status code should be "404"
    Given I use a valid JWT Bearer token for "Owner"
    When I request "GET" "/api/projects/2"
    Then the response status code should be "200"
