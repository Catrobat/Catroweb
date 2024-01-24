@api @user
Feature: Get logged in user

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser  | 54321    | bbbbbbbbbb | 3  |
      | Catroweb | 54321    | bbbbbbbbbb | 4  |
    And there are followers:
      | name     | following       |
      | Catrobat | User1, Catroweb |
      | NewUser  | Catrobat        |
      | Catroweb | User1, NewUser  |
    And there are projects:
      | id        | name     | owned by | version | private | visible |
      | isxs-adkt | Webteam  | Catroweb | 0.8.5   | false   | true    |
      | tvut-irkw | Catroweb | NewUser  | 0.8.5   | false   | true    |
    And I wait 1000 milliseconds

  Scenario: Get logged in user
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/user"
    Then the response status code should be "200"
    Then the response should have the default extended user model structure
    Then the response should contain the following user:
      | Name     |
      | Catrobat |

  Scenario: Get user without logging in should return 401 status code
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/user"
    Then the response status code should be "401"

  Scenario: Get user with invalid JWT Bearer token should return 401 status code
    Given I use an invalid JWT Bearer token
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/user"
    Then the response status code should be "401"

  Scenario: Get user without setting accept header should return 406 status code
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I use a valid JWT Bearer token for "Catrobat"
    When I request "GET" "/api/user"
    Then the response status code should be "406"
