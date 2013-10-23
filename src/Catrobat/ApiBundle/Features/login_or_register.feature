@api
Feature: Login with an existing accunt or register a new one

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 123456   | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: Register a new user
    Given I have a parameter "registrationUsername" with value "newuser"
    And I have a parameter "registrationPassword" with value "registrationpassword"
    And I have a parameter "registrationEmail" with value "test@mail.com"
    And I have a parameter "registrationCountry" with value "at"
    When I POST these parameters to "/api/loginOrRegister/loginOrRegister.json"
    Then I should get the json object with random token:
      """
      {"token":"<token>","statusCode":201,"answer":"Registration successful!","preHeaderMessages":""}
      """

  @TODO
  Scenario: login with valid username and password
    Given I have a parameter "registrationUsername" with value "Catrobat"
    And I have a parameter "registrationPassword" with value "123456"
    When I POST these parameters to "/api/loginOrRegister/loginOrRegister.json"
    Then I should see:
      """
      {"token":"cccccccccc","statusCode":200,"preHeaderMessages":""}
      """

  @TODO
  Scenario: loginOrRegister with invalid login
    Given I have a parameter "registrationUsername" with value "Catrobat"
    And I have a parameter "registrationPassword" with value "invalid"
    When I POST these parameters to "/api/loginOrRegister/loginOrRegister.json"
    Then I should see:
      """
      {"statusCode":601,"answer":"The password or username was incorrect.","preHeaderMessages":""}
      """
      
  Scenario: Trying to register without password should fail
    Given I have a parameter "registrationUsername" with value "newuser"
    And I have a parameter "registrationPassword" with value ""
    And I have a parameter "registrationEmail" with value "test@mail.com"
    And I have a parameter "registrationCountry" with value "at"
    When I POST these parameters to "/api/loginOrRegister/loginOrRegister.json"
    Then I should see:
      """
      {"statusCode":602,"answer":"The password is missing.","preHeaderMessages":""}
      """
      
  Scenario: Trying to register with a password smaller then six characters
    Given I have a parameter "registrationUsername" with value "newuser"
    And I have a parameter "registrationPassword" with value "123"
    And I have a parameter "registrationEmail" with value "test@mail.com"
    And I have a parameter "registrationCountry" with value "at"
    When I POST these parameters to "/api/loginOrRegister/loginOrRegister.json"
    Then I should see:
      """
      {"statusCode":602,"answer":"Your password must have at least 6 characters.","preHeaderMessages":""}
      """

  Scenario: Trying to register without country should fail
    Given I have a parameter "registrationUsername" with value "newuser"
    And I have a parameter "registrationPassword" with value "123456"
    And I have a parameter "registrationEmail" with value "test@mail.com"
    And I have a parameter "registrationCountry" with value ""
    When I POST these parameters to "/api/loginOrRegister/loginOrRegister.json"
    Then I should see:
      """
      {"statusCode":602,"answer":"The country is missing.","preHeaderMessages":""}
      """

      

