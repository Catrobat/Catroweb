@api
Feature: Pocketcode API

  Background: 
    Given there are users:
      | name       | password | token        |
      | "Catrobat" | "12345"  | "cccccccccc" |
      | "User1"    | "vwxyz"  | "aaaaaaaaaa" |
    And there are projects:
      | name        | owned by   | downloads | upload time      |
      | "project 1" | "Catrobat" | 3         | 01.01.2013 12:00 |
      | "project 2" | "Catrobat" | 33        | 01.02.2013 13:00 |
      | "project 3" | "User1"    | 133       | 01.01.2012 13:00 |

  ####### check token #######
  Scenario: Checking the current token
    Given I am "Catrobat"
    When I call "/api/checkToken/check.json" with token "cccccccccc"
    Then I should see:
      """
      {"statusCode":200,"answer":"ok","preHeaderMessages":"  \n"}
      """

  Scenario: Checking an invalid token
    Given I am "Catrobat"
    When I call "/api/checkToken/check.json" with token "invalid"
    Then I should see:
      """
      {"statusCode":601,"answer":"Sorry, your authentication data was incorrect. Please check your nickname and password!","preHeaderMessages":"  \n"}
      """

  ####### login or register #######
  @TODO
  Scenario: loginOrRegister with new user
    Given I am not registered
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "newuser" and password "newpassword"
    Then I should see:
      """

      """

  @TODO
  Scenario: loginOrRegister with valid login
    Given I am "Catrobat"
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "Catrobat" and password "12345"
    Then I should see:
      """

      """

  @TODO
  Scenario: loginOrRegister with invalid login
    Given I am "Catrobat"
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "Catrobat" and password "invalid"
    Then I should see:
      """

      """

  ####### project upload #######
  @TODO
  Scenario: loginOrRegister with invalid login
    Given I am "Catrobat"
    When I use "Catrobat" as username
    And I use "cccccccccc" as token
    And I want to upload the file "test.catrobat"
    When I call "/api/upload/upload.json" with given data
    Then I should see:
      """

      """

  ####### project search #######
  @TODO
  Scenario: search projects

  ####### recent projects #######
  @TODO
  Scenario: show recent projects
