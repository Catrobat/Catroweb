@api
Feature: Login with an existing account or register a new one

  Background:
    Given there are users:
      | name      | password | token      | dn           | email                      |
      | Catrobat  | 123456   | cccccccccc |              | default1@pocketcode.org    |
      | LDAP-user |          | cccccccccc | cn=LDAP-user | casdasdsassadsada@d.com    |

  Scenario: Register
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
      | name                 | value                |
      | registrationUsername | newuser              |
      | registrationPassword | registrationpassword |
      | registrationCountry  | AT                   |
      | registrationEmail    | newuser@mail.at      |
    When I POST these parameters to "/pocketcode/api/register/Register.json"
    Then I should get the json object:
      """
      {"token":"rrrrrrrrrrr","statusCode":201,"answer":"Registration successful!","preHeaderMessages":""}
      """


  Scenario: Register but username exists
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
      | name                 | value                |
      | registrationUsername | Catrobat             |
      | registrationPassword | registrationpassword |
      | registrationCountry  | AT                   |
      | registrationEmail    | newuser@mail.at      |
    When I POST these parameters to "/pocketcode/api/register/Register.json"
    Then I should get the json object:
      """
      {"statusCode":777,"answer":"This username already exists.","preHeaderMessages":""}
      """


  Scenario: Register but email exists
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
      | name                 | value                        |
      | registrationUsername | newuser                      |
      | registrationPassword | registrationpassword         |
      | registrationCountry  | AT                           |
      | registrationEmail    | default1@pocketcode.org      |
    When I POST these parameters to "/pocketcode/api/register/Register.json"
    Then I should get the json object:
      """
      {"statusCode":757,"answer":"This email address already exists.","preHeaderMessages":""}
      """

  Scenario: Log in with existing account
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
      | name                 | value    |
      | registrationUsername | Catrobat |
      | registrationPassword | 123456   |
    When I POST these parameters to "/pocketcode/api/login/Login.json"
    Then I should get the json object:
      """
      {"statusCode":200, "token": "rrrrrrrrrrr", "email":"default1@pocketcode.org","nolbUser":false,"preHeaderMessages":""}
      """

  Scenario: Log in user not exists
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
      | name                 | value    |
      | registrationUsername | Random   |
      | registrationPassword | 123456   |
    When I POST these parameters to "/pocketcode/api/login/Login.json"
    Then I should get the json object:
      """
      {"statusCode":764, "answer":"This username does not exist.","preHeaderMessages":""}
      """

  Scenario: Log in user wrong password
    Given the next generated token will be "rrrrrrrrrrr"
    And I have the POST parameters:
      | name                 | value    |
      | registrationUsername | Catrobat |
      | registrationPassword | afdsafds |
    When I POST these parameters to "/pocketcode/api/login/Login.json"
    Then I should get the json object:
      """
      {"statusCode":601, "answer":"The password or username was incorrect.","preHeaderMessages":""}
      """