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
        "password": "Password missing",
        "date_of_birth": "Date of birth is required"
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
        "password": "Password too short",
        "date_of_birth": "Date of birth is required"
      }
    """

  Scenario: Too long username and password should result in an error
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "ca111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111",
        "password": "12334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567123345671233456712334567"
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
        "password": "Password too long",
        "date_of_birth": "Date of birth is required"
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
        "username": "Username already in use",
        "date_of_birth": "Date of birth is required"
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
    And the client response should contain "E-Mail wird bereits verwendet"
    And the client response should contain "Benutzername wird bereits ben"
    And the client response should contain "date_of_birth"


  Scenario: Dry-running with valid request fields should return no error and not create a user
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "Testuser",
        "password": "1234567",
        "date_of_birth": "2000-01-15"
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
        "password": "1234567",
        "date_of_birth": "2000-01-15"
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
        "password": "1234567",
        "date_of_birth": "2000-01-15"
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
        "password": "1234567",
        "date_of_birth": "2000-01-15"
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
        "password": "Password missing",
        "date_of_birth": "Date of birth is required"
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
        "password": "1234asdf",
        "date_of_birth": "2000-01-15"
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

  Scenario: Registering a user should send an email to verify the account
    Given I have the following JSON request body:
    """
      {
        "email": "test@test.at",
        "username": "Testuser",
        "password": "1234567",
        "date_of_birth": "2000-01-15"
      }
    """
    And the current time is "01.08.2014 14:00"
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
    And the user "Testuser" should have a verification email requested at "1406901600"

  Scenario: Registration without date of birth should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "Testuser2",
        "password": "123456"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "date_of_birth"

  Scenario: Registration with invalid date of birth should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "Testuser2",
        "password": "123456",
        "date_of_birth": "not-a-date"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "date_of_birth"

  Scenario: Registration with too young user should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "test@test.at",
        "username": "Testuser2",
        "password": "123456",
        "date_of_birth": "2025-01-01"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "date_of_birth"

  Scenario: Registration with valid adult date of birth should succeed
    Given I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "adult@test.at",
        "username": "AdultUser",
        "password": "123456",
        "date_of_birth": "2000-01-15"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "201"

  Scenario: Registration as child without parent email should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "child@test.at",
        "username": "ChildUser",
        "password": "123456",
        "date_of_birth": "2018-06-15"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "parent_email"

  Scenario: Registration as child with parent email should succeed
    Given I have the following JSON request body:
    """
      {
        "dry_run": false,
        "email": "child@test.at",
        "username": "ChildUser",
        "password": "123456",
        "date_of_birth": "2018-06-15",
        "parent_email": "parent@test.at"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "201"

  Scenario: Registration as child with invalid parent email should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "child@test.at",
        "username": "ChildUser",
        "password": "123456",
        "date_of_birth": "2018-06-15",
        "parent_email": "not-an-email"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "parent_email"

  Scenario: Registration as child with parent email same as user email should fail
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "child@test.at",
        "username": "ChildUser2",
        "password": "123456",
        "date_of_birth": "2018-06-15",
        "parent_email": "child@test.at"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "parent_email"

  Scenario: Registration as 13-year-old requires parent email (under-14 threshold)
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "teen@test.at",
        "username": "TeenUser",
        "password": "123456",
        "date_of_birth": "2013-06-15"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "422"
    And the client response should contain "parent_email"

  Scenario: Registration as 14-year-old does not require parent email
    Given I have the following JSON request body:
    """
      {
        "dry_run": true,
        "email": "teen14@test.at",
        "username": "Teen14User",
        "password": "123456",
        "date_of_birth": "2012-01-15"
      }
    """
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/user"
    Then the response status code should be "204"
