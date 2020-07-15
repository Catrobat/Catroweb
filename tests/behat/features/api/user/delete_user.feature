@api @user
Feature: Delete logged in user

  Background:
    Given there are users:
      | name         | password | token      | id |
      | Catrobat     | 12345    | cccccccccc | 1  |
      | User1        | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser      | 54321    | bbbbbbbbbb | 3  |
      | Catroweb     | 54321    | bbbbbbbbbb | 4  |
    And there are followers:
      | name     | following      |
      | Catrobat | User1, Catroweb|
      | NewUser  | Catrobat       |
      | Catroweb | User1, NewUser |
    And there are programs:
      | id        | name            | owned by | version | private | visible |
      | isxs-adkt | Webteam         | Catroweb | 0.8.5   | false   | true    |
      | tvut-irkw | Catroweb        | NewUser  | 0.8.5   | false   | true    |
    And I wait 1000 milliseconds

  Scenario: Delete logged in user
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/user"
    Then the response status code should be "204"
    And the user "Catrobat" should not exist

  Scenario: Delete user without JWT Bearer token should return 401 status code
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/user"
    Then the response status code should be "401"

  Scenario: Delete user with invalid JWT Bearer token should return 401 status code
    Given I use an invalid JWT Bearer token
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/user"
    Then the response status code should be "401"
