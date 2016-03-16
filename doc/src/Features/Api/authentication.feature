@doc
Feature: Authentication to the system
  How to register and login to the system

  Background:
   
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |


  Scenario: Registration of a new user
    Given I have the HTTP Request:
          | Method | POST                                                 |
          | Url    | /pocketcode/api/loginOrRegister/loginOrRegister.json |
      And I use the POST parameters:
          | Name                 | Value                |
          | registrationUsername | newuser              |
          | registrationPassword | registrationpassword |
          | registrationEmail    | test@mail.com        |
          | registrationCountry  | at                   |
      And We assume the next generated token will be "rrrrrrrrrrr"
     When I invoke the Request
     Then I will get the json object:
          """
          {"token":"rrrrrrrrrrr","statusCode":201,"answer":"Registration successful!","preHeaderMessages":""}
          """
          
   Scenario Outline: Troubleshooting
       Given There is a registration problem <problem>
        When I invoke the Request
        Then I will get the json object:
         """
        {"statusCode":"<errorcode>","answer":"<answer>","preHeaderMessages":""}
        """
        
        Examples:
        | problem            | errorcode | answer                   |
        | no password given | 602       | The password is missing. |
        
  Scenario: Retrieve the upload token of an user
    Given I have the HTTP Request:
          | Method | POST                                                 |
          | Url    | /pocketcode/api/loginOrRegister/loginOrRegister.json |
      And I use the POST parameters:
          | name                 | value                 |
          | registrationUsername | Catrobat              |
          | registrationPassword | 12345                 |
     When I invoke the Request
     Then I will get the json object:
          """
          {"token":"cccccccccc","statusCode":200,"preHeaderMessages":""}
          """

  Scenario: Checking a given token for its validity
    Given I have the HTTP Request:
          | Method | POST                                  |
          | Url    | /pocketcode/api/checkToken/check.json |
      And I use the POST parameters:
          | username | Catrobat   |
          | token    | cccccccccc |
     When I invoke the Request
     Then I will get the json object:
          """
          {
            "statusCode": 200,
            "answer": "ok",
            "preHeaderMessages": "  \n"
          }
          """
       And The response code will be "200"

   Scenario Outline: Troubleshooting
       Given There is a check token problem <problem>
        When I invoke the Request
        Then I will get the json object:
             """
             {"statusCode":"<errorcode>","answer":"<answer>","preHeaderMessages":""}
             """
        And The response code will be "<httpcode>"
        
        Examples:
        | problem            | errorcode | answer                                               | httpcode  |
        | invalid token      | 601       | Authentication of device failed: invalid auth-token! | 401       |

