Feature:

Background: 
    Given I there is a user with
          | Property | value     |
          | username | TokenUser |
          | token    | generated |

Scenario:
    Given I have a HTTP Request:
          | Method | GET                    |
          | Url    | /pocketcode/tokenlogin |
      And I use the GET parameters:
          | username | TokenUser |
          | token    | generated |
     When I invoke the Request
     Then I should be on "/pocketcode/?login"
      And I should be logged in

Scenario:
    Given I have a HTTP Request:
          | Method | GET                    |
          | Url    | /pocketcode/tokenlogin |
      And I use the GET parameters:
          | username | TokenUser |
          | token    | INVALID   |
     When I invoke the Request
     Then I should be on "/pocketcode/?redirect"
      And I should be logged out
      