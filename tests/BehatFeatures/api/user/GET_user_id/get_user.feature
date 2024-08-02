@api @user
Feature: Get user by id

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
    And I wait 500 milliseconds

  Scenario: Get user by id

    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/user/2"
    Then the response status code should be "200"
    Then the response should have the user model structure excluding "picture"
    Then the response should contain the following user:
      | Name     |
      | Catrobat |

  Scenario: User not found should return 404 status code

    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/user/5"
    Then the response status code should be "404"

  Scenario: Get user without setting accept header should return 406 status code
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    When I request "GET" "/api/user/2"
    Then the response status code should be "406"