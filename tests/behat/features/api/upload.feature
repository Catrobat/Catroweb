@api @upload
Feature: Upload a program to the website

  Background:

    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc |  1 |
      | User1    | vwxyz    | aaaaaaaaaa |  2 |

  Scenario: Upload program
    Given the HTTP Request:
      | Method | POST                         |
      | Url    | /app/api/upload/upload.json  |
    And the POST parameters:
      | Name         | Value                  |
      | username     | Catrobat               |
      | token        | cccccccccc             |
      | fileChecksum | <md5 checksum of file> |
    And a catrobat file is attached to the request
    And the POST parameter "fileChecksum" contains the MD5 sum of the attached file
    And we assume the next generated token will be "rrrrrrrrrrr"
    When the Request is invoked
    Then the returned json object with id "1" will be:
          """
          {
            "projectId": "1",
            "statusCode": 200,
            "answer": "Your project was uploaded successfully!",
            "token": "rrrrrrrrrrr",
            "preHeaderMessages": ""
          }
          """

  Scenario Outline: Troubleshooting
    Given the upload problem "<problem>"
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
      | problem              | errorcode | answer                                               |
      | no authentication    | 601       | Authentication of device failed: invalid auth-token! |
      | missing parameters   | 501       | POST-Data not correct or missing!                    |
      | invalid program file | 505       | invalid file                                         |
