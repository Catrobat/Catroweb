@api
Feature: Login with an existing accunt or register a new one

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 123456   | cccccccccc |


  Scenario: Register a new user
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
        | name                 | value                |
        | registrationUsername | newuser              |
        | registrationPassword | registrationpassword |
        | registrationEmail    | test@mail.com        |
        | registrationCountry  | at                   |
    When I POST these parameters to "/pocketcode/api/loginOrRegister/loginOrRegister.json"
    Then I should get the json object:
      """
      {"token":"rrrrrrrrrrr","statusCode":201,"answer":"Registration successful!","preHeaderMessages":""}
      """


  Scenario: login with valid username and password
    Given I have the POST parameters:
        | name                 | value                 |
        | registrationUsername | Catrobat              |
        | registrationPassword | 123456                |
    When I POST these parameters to "/pocketcode/api/loginOrRegister/loginOrRegister.json"
    Then I should get the json object:
      """
      {"token":"cccccccccc","statusCode":200,"preHeaderMessages":""}
      """


  Scenario: loginOrRegister with invalid login
    Given I have the POST parameters:
        | name                 | value                 |
        | registrationUsername | Catrobat              |
        | registrationPassword | invalid               |
    When I POST these parameters to "/pocketcode/api/loginOrRegister/loginOrRegister.json"
    Then I should get the json object:
      """
      {"statusCode":601,"answer":"The password or username was incorrect.","preHeaderMessages":""}
      """


  Scenario: Trying to register without password should fail
    When I try to register without a password
    Then I should get the json object:
      """
      {"statusCode":602,"answer":"The password is missing.","preHeaderMessages":""}
      """


  Scenario: Trying to register without country should fail
    When I try to register without a country
    Then I should get the json object:
      """
      {"statusCode":602,"answer":"The country is missing.","preHeaderMessages":""}
      """


  Scenario: Trying to register with a password smaller then six characters
    Given I have otherwise valid registration parameters
    But I have a parameter "registrationPassword" with value "123"
    When I try to register
    Then I should get the json object:
      """
      {"statusCode":753,"answer":"Your password must have at least 6 characters.","preHeaderMessages":""}
      """

  Scenario: Trying to register with an invalid email should fail 
    Given I have otherwise valid registration parameters
    But I have a parameter "registrationEmail" with value "invalid#mail"
    When I try to register
    Then I should get the json object:
      """
      {"statusCode":765,"answer":"Your email seems to be invalid","preHeaderMessages":""}
      """


  Scenario: an email address can only be used by one user
    When I register a new user
    And I try to register another user with the same email adress
    Then I should get the json object:
      """
      {"statusCode":757,"answer":"This email address already exists.","preHeaderMessages":""}
      """


