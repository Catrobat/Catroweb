@api @user
Feature: Registering a new user.

  Background:
    Given there are users:
      | id | name     | password | email                 |
      | 1  | Catroweb | 123456   | catroweb@localhost.at |

  Scenario: The Content type of the request must be application/json
    Given I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "test@test.at",
        "username": "Testuser",
        "password": "123456"
      }
    """
    And I request "POST" "/api/user"
    Then the response status code should be "415"

  Scenario: An invalid request should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "test@test.at",
        "username": "",
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "400"

  Scenario: Empty request fields should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "",
        "username": "",
        "password": ""
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email missing",
        "username": "Username missing",
        "password": "Password missing"
      }
    """

  Scenario: Invalid email and too short username and password should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "testqtest.at",
        "username": "ca",
        "password": "123"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email invalid",
        "username": "Username too short",
        "password": "Password too short"
      }
    """

  Scenario: Too long username and password should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "ca111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111",
        "password": "12334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "username": "Username too long",
        "password": "Password too long"
      }
    """

  Scenario: Trying to register with already existing usernames and emails should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "catroweb@localhost.at",
        "username": "Catroweb",
        "password": "1234567"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email already in use",
        "username": "Username already in use"
      }
    """

  Scenario: Trying to register with already existing usernames and emails and accept-language
  header set to german should result in an german error
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "catroweb@localhost.at",
        "username": "Catroweb",
        "password": "1234567"
      }
    """
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "email": "E-Mail wird bereits verwendet",
        "username": "Benutzername wird bereits benützt"
      }
    """


  Scenario: Dry-running with valid request fields should return no error and not create a user
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "Testuser",
        "password": "1234567"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "204"
    And the user "Testuser" should not exist

  Scenario: Registering a user should work and return a login token
    Given I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "test@test.at",
        "username": "Testuser",
        "password": "1234567"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "201"
    And I should get the json object:
    """
      {
        "token": "REGEX_STRING_WILDCARD",
        "refresh_token": "REGEX_STRING_WILDCARD"
      }
    """
    And the user "Testuser" with email "test@test.at" should exist and be enabled


  Scenario: Trying to register user with username that contains an email address should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "catro@localhost.at",
        "username": "catroweb@localhost.at",
        "password": "1234567"
      }
    """

    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "username": "Username must not contain an email address"
      }
    """

  Scenario: Trying to register user with username that starts with Scratch:
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "catro@localhost.at",
        "username": "Scratch: user",
        "password": "1234567"
      }
    """

    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "username": "Username invalid"
      }
    """

  Scenario: Missing request fields should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": false
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email missing",
        "username": "Username missing",
        "password": "Password missing"
      }
    """


  Scenario: Trying to send an invalid request, without HTTP_ACCEPT, should return json as response
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I have the following JSON request body:
    """
      {
        "dry_run": false,
        "username": "Catroweb",
      }
    """
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "406"

  Scenario: Invalid TLD in email

  Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "testqtest.invalid",
        "username": "invalidTLD",
        "password": "1234asdf"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "email": "Email invalid"
      }
    """
