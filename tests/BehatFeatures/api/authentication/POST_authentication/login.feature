@api @authentication
Feature: Login with valid credentials should return a valid JWT token

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |

  Scenario: A valid request should return a JWT token
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
        "token": "REGEX_STRING_WILDCARD"
     }
    """

  Scenario: A missing request body should result in an error
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/authentication"
    Then the response status code should be "400"

  Scenario: A missing request body parameter should result in an error
    Given I have the following JSON request body:
    """
      {
        "username": "Catrobat"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And  I request "POST" "/api/authentication"
    Then the response status code should be "400"

  Scenario: A missing request body parameter should result in an error
    Given I have the following JSON request body:
    """
      {
        "password": "123456"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/authentication"
    Then the response status code should be "400"

  Scenario: A missing request body parameter should result in an error
    Given I have the following JSON request body:
    """
      {
        "username2": "Catrobat",
        "password": "123456"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/authentication"
    Then the response status code should be "400"

  Scenario: Invalid credentials should result in an error
    Given I have the following JSON request body:
    """
      {
        "username": "NonExistent",
        "password": "123456"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/authentication"
    Then the response status code should be "401"

  Scenario: Invalid credentials should result in an error
    Given I have the following JSON request body:
    """
      {
        "username": "Catrobat",
        "password": "WrongPassword"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/authentication"
    Then the response status code should be "401"

  Scenario: A valid request should update the last login date of a user
    Given the user "Catrobat" should have a last login date with the value "null"
    And the current time is "06.07.2032 13:00"
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
        "token": "REGEX_STRING_WILDCARD"
     }
    """
    And the user "Catrobat" should have a last login date with the value "1972731600"
