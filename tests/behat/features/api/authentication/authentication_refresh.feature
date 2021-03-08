@api @authentication
Feature: Login with valid credentials should return a valid JWT token & refresh Token

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |

    Given there are refresh_tokens:
  |id	|refresh_token	|username	|valid  |
  |1	|valid      	|Catrobat	| 1     |
  |2	|invalid      	|Catrobat	| 0     |

  Scenario: A valid request should return a JWT token & refresh token
    Given I have the following JSON request body:
    """
          {
            "username": "Catrobat",
            "password": "123456"
          }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/authentication"
    Then the response status code should be "200"
    And I should get the json object:
    """
         {
            "token": "REGEX_STRING_WILDCARD",
            "refresh_token": "REGEX_STRING_WILDCARD"
         }
    """

  Scenario: A valid request with refresh token
    Given I have the following JSON request body:
    """
    {
        "refresh_token": "valid"
    }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "api/authentication/refresh"
    Then the response status code should be "200"
    And I should get the json object:
    """
         {
            "token": "REGEX_STRING_WILDCARD",
            "refresh_token": "REGEX_STRING_WILDCARD"
         }
    """

  Scenario: A invalid request with refresh token
    Given I have the following JSON request body:
    """
    {
        "refresh_token": "invalid"
    }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "api/authentication/refresh"
    Then the response status code should be "401"
