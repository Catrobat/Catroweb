@api @authentication
Feature: To ease the upgrade process a deprecated upload token can be upgraded to a JWT

  Background:
    Given there are users:
      | id | name     | token                  |
      | 1  | Catrobat | I_am_the_correct_token |

  Scenario: Request must use application/json as content type
    Given I have the following JSON request body:
    """
      {
        "upload_token": "I_am_the_correct_token"
      }
    """
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/upgrade"
    Then the response status code should be "415"

  Scenario: Client must accept application/json as content type
    Given I have the following JSON request body:
    """
      {
        "upload_token": "I_am_the_correct_token"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "POST" "/api/authentication/upgrade"
    Then the response status code should be "406"

  Scenario: A missing request body should result in an error
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/upgrade"
    Then the response status code should be "400"

  Scenario: A missing/wrong request body parameter should result in an error
    Given I have the following JSON request body:
    """
      {
        "token": "I_am_the_correct_token"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And  I request "POST" "/api/authentication/upgrade"
    Then the response status code should be "400"

  Scenario: Invalid credentials should result in an error
    Given I have the following JSON request body:
    """
      {
        "upload_token": "I_am_no_valid_token"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/upgrade"
    Then the response status code should be "401"

  Scenario: A correct upload token will be exchanged with a JWT token
    Given I have the following JSON request body:
    """
      {
        "upload_token": "I_am_the_correct_token"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/authentication/upgrade"
    Then the response status code should be "200"
    And I should get the json object:
    """
     {
        "token": "REGEX_STRING_WILDCARD",
        "refresh_token": "REGEX_STRING_WILDCARD"
     }
    """
