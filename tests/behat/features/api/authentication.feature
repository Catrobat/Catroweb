@api
Feature: Authenticate to the system
  How to register and login to the system

  Background:

    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc |  1 |
      | User1    | vwxyz    | aaaaaaaaaa |  2 |


  Scenario: Registration of a new user
    Given the HTTP Request:
      | Method | POST                                          |
      | Url    | /app/api/loginOrRegister/loginOrRegister.json |
    And the POST parameters:
      | Name                 | Value                |
      | registrationUsername | newuser              |
      | registrationPassword | registrationpassword |
      | registrationEmail    | test@mail.com        |
      | registrationCountry  | at                   |
    And we assume the next generated token will be "rrrrrrrrrrr"
    When the Request is invoked
    Then I should get the json object:
          """
          {
            "statusCode": 201,
            "answer": "Registration successful!",
            "preHeaderMessages": "",
            "token": "rrrrrrrrrrr"
          }
          """

  Scenario Outline: Troubleshooting
    Given the registration problem "<problem>"
    When such a Request is invoked
    Then I should get the json object:
          """
          {
            "statusCode": <errorcode>,
            "answer": "<answer>",
            "preHeaderMessages": ""
          }
          """

    Examples:
      | problem           | errorcode | answer                   |
      | no password given | 602       | The password is missing. |

  Scenario: Retrieve the upload token of a user
    Given the HTTP Request:
      | Method | POST                                                 |
      | Url    | /app/api/loginOrRegister/loginOrRegister.json |
    And the POST parameters:
      | name                 | value    |
      | registrationUsername | Catrobat |
      | registrationPassword | 12345    |
    When the Request is invoked
    Then I should get the json object:
          """
          {
            "statusCode": 200,
            "token": "cccccccccc",
            "preHeaderMessages": ""
          }
          """

  Scenario: Checking a given token for its validity
    Given the HTTP Request:
      | Method | POST                                  |
      | Url    | /app/api/checkToken/check.json |
    And the POST parameters:
      | Name     | Value      |
      | username | Catrobat   |
      | token    | cccccccccc |
    When the Request is invoked
    Then I should get the json object:
          """
          {
            "statusCode": 200,
            "answer": "ok",
            "preHeaderMessages": "  \n"
          }
          """
    And the response code will be "200"

  Scenario Outline: Troubleshooting
    Given the check token problem "<problem>"
    When such a Request is invoked
    Then I should get the json object:
          """
          {
            "statusCode": <errorcode>,
            "answer": "<answer>",
            "preHeaderMessages": ""
          }
          """
    And the response code will be "<httpcode>"

    Examples:
      | problem       | errorcode | answer                    | httpcode |
      | invalid token | 601       | Upload Token auth failed. | 401      |

  Scenario: Registration of a new user with a too long username
    Given the HTTP Request:
      | Method | POST                                                 |
      | Url    | /app/api/register/Register.json |
    And the POST parameters:
      | Name                 | Value                |
      | registrationUsername | aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa              |
      | registrationPassword | registrationpassword |
      | registrationEmail    | test@mail.com        |
      | registrationCountry  | at                   |
    And we assume the next generated token will be "rrrrrrrrrrr"
    When the Request is invoked
    Then I should get the json object:
          """
          {
            "statusCode": 602,
            "answer": "This value is not valid.",
            "preHeaderMessages": ""
          }
          """
