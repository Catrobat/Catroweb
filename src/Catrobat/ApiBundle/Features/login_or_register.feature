@api
Feature: Login with an existing accunt or register a new one

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: loginOrRegister with new user
    Given I am not registered
    And I have a username "newuser"
    And I have a password "newpassword"
    And I have a language "en"
    And I have an email address "test@wherever.com"
    When I call "/api/loginOrRegister/loginOrRegister.json" with the given data
    Then I should see:
      """
      {"token":"<token>","statusCode":201,"answer":"Registration successful!","preHeaderMessages":""}
      """

  @TODO
  Scenario: loginOrRegister with valid login
    Given I am "Catrobat"
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "Catrobat" and password "12345"
    Then I should see:
      """
      {"token":"cccccccccc","statusCode":200,"preHeaderMessages":""}
      """

  @TODO
  Scenario: loginOrRegister with invalid login
    Given I am "Catrobat"
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "Catrobat" and password "invalid"
    Then I should see:
      """
      {"statusCode":601,"answer":"The password or username was incorrect.","preHeaderMessages":""}
      """

