@api @authentication
Feature: Checking a JWT token validity

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: A valid authentication header in the request should result in a success response.
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "GET" "/api/authentication"
    Then the response status code should be "200"

  Scenario: Missing authentication in the request should result in an error.
    Given I request "GET" "/api/authentication"
    Then the response status code should be "401"

  Scenario: Empty authentication header in the request should result in an error.
    Given I use an empty JWT Bearer token
    And I request "GET" "/api/authentication"
    Then the response status code should be "401"

  Scenario: Invalid authentication header in the request should result in an error.
    Given I use an invalid JWT Bearer token
    And I request "GET" "/api/authentication"
    Then the response status code should be "401"

  Scenario: Expired authentication header in the request should result in an error.
    Given I use an expired JWT Bearer token for "Catrobat"
    And I request "GET" "/api/authentication"
    Then the response status code should be "401"

  Scenario: An authentication header of a non-existent user in the request should result in an error.
    Given I use a valid JWT Bearer token for "NonExistent"
    And I request "GET" "/api/authentication"
    Then the response status code should be "401"
