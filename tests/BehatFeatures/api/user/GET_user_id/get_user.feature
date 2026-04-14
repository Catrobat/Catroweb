@api @user
Feature: Get user by id

  Background:
    Given there are users:
      | name     | password | id |
      | Catrobat | 12345    | 1  |
      | User1    | vwxyz    | 2  |
      | NewUser  | 54321    | 3  |
      | Catroweb | 54321    | 4  |
    And there are followers:
      | name     | following       |
      | Catrobat | User1, Catroweb |
      | NewUser  | Catrobat        |
      | Catroweb | User1, NewUser  |
    And there are projects:
      | id        | name     | owned by | version | private | visible |
      | isxs-adkt | Webteam  | Catroweb | 0.8.5   | false   | true    |
      | tvut-irkw | Catroweb | NewUser  | 0.8.5   | false   | true    |
    And I wait 500 milliseconds

  Scenario: Get user by id
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/2"
    Then the response status code should be "200"
    Then the response should have the user model structure excluding "avatar"
    Then the response should contain the user "User1"

  Scenario: User not found should return 404 status code
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/users/5"
    Then the response status code should be "404"

  Scenario: Hidden profile returns 404 for public callers
    Given the users are profile-hidden:
      | name  |
      | User1 |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/users/2"
    Then the response status code should be "404"

  Scenario: Hidden profile is accessible to owner
    Given the users are profile-hidden:
      | name  |
      | User1 |
    And I use a valid JWT Bearer token for "User1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/users/2"
    Then the response status code should be "200"
    And the response should contain the user "User1"

  Scenario: Hidden profile is accessible to admins
    Given there are admins:
      | name  |
      | Admin |
    And the users are profile-hidden:
      | name  |
      | User1 |
    And I use a valid JWT Bearer token for "Admin"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/users/2"
    Then the response status code should be "200"
    And the response should contain the user "User1"

  Scenario: Get user without setting accept header should return 406 status code
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    When I request "GET" "/api/users/2"
    Then the response status code should be "406"
