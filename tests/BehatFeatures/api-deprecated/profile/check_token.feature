@api
Feature: Checking a user's token validity

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario Outline: Checking the current token
    Given I have a parameter "username" with value "<username>"
    And I have a parameter "token" with value "<token>"
    When I POST these parameters to "/app/api/checkToken/check.json"
    Then I should get the json object:
      """
      {"statusCode":200,"answer":"ok","preHeaderMessages":"  \n"}
      """
    And the response code should be "200"

    Examples:
      | username | token      |
      | Catrobat | cccccccccc |
      | User1    | aaaaaaaaaa |

  Scenario: Checking an invalid token
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "invalid"
    When I POST these parameters to "/app/api/checkToken/check.json"
    And the response code should be "403"

  Scenario: Checking the token of a non-existing user should return an error
    Given I have a parameter "username" with value "doesnotexist"
    And I have a parameter "token" with value "doesnotmatter"
    When I POST these parameters to "/app/api/checkToken/check.json"
    And the response code should be "403"

  Scenario: Sending no token must fail
    When I POST these parameters to "/app/api/checkToken/check.json"
    And the response code should be "401"
