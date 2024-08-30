@api @user
Feature: Update user

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser  | 54321    | bbbbbbbbbb | 3  |
      | Catroweb | 54321    | bbbbbbbbbb | 4  |
    And I wait 500 milliseconds

  Scenario: Update user with dry_run option
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": true,
        "username": "User2"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "204"

  Scenario: Update user username
    Given I use a valid JWT Bearer token for "NewUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "username": "User2"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "204"
    And the following users exist in the database:
      | name  |
      | User2 |
    And the user "NewUser" should not exist

  Scenario: Update user username with an existing username
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "username": "Catroweb"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "username": "Username already in use"
      }
    """

  Scenario: Update user with invalid username
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "username": "Scratch: admin"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "username": "Username invalid"
      }
    """

  Scenario: Update user password
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "current_password": "12345",
        "password": "123456"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "204"
    And the following users exist in the database:
      | name     | password |
      | Catrobat | 123456   |

  Scenario: Update user password without current password
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "password": "123456"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "current_password": "Current password is missing"
      }
    """

  Scenario: Update user password with invalid password
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "current_password": "12345",
        "password": ""
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "password": "Password cannot be empty"
      }
    """

  Scenario: Update user email
    Given I use a valid JWT Bearer token for "Catroweb"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "user@catrobat.at"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "204"
    And user "Catroweb" with email "user@catrobat.at" should exist

  Scenario: Update user with invalid email
    Given I use a valid JWT Bearer token for "Catroweb"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "email": "catroweb"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email invalid"
      }
    """

  Scenario: Update user email with an existing email
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "User1@catrobat.at"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email already in use"
      }
    """

  Scenario: Update user with invalid JWT Bearer token should return 401 status code
    Given I use an invalid JWT Bearer token
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": true,
        "username": "User2"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "401"

  Scenario: Update user with expired JWT Bearer token should return 401 status code
    Given I use an invalid JWT authorization header for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": true,
        "username": "User2"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "401"

  Scenario: Update user without setting content-type header should return 415 status code
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "dry_run": true,
        "username": "User2"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "415"

  Scenario: Update email with invalid TLD
    Given I use a valid JWT Bearer token for "Catroweb"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "email": "user@catrobat.invalid"
      }
    """
    And I request "PUT" "/api/user"
    Then the response code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email invalid"
      }
    """
