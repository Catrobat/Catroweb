@api
Feature: Checking a user's token validity

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

  Scenario: Checking the current token
    Given I am "Catrobat"
    When I call "/api/checkToken/check.json" with token "cccccccccc"
    Then I should see:
      """
      {"statusCode":200,"answer":"ok","preHeaderMessages":"  \n"}
      """
    And the response code should be "200"

  Scenario: Checking the current token
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    When I POST these parameters to "/api/checkToken/check.json"
    Then I should see:
      """
      {"statusCode":200,"answer":"ok","preHeaderMessages":"  \n"}
      """
    And the response code should be "200"

  Scenario: Checking an invalid token
    Given I am "Catrobat"
    When I call "/api/checkToken/check.json" with token "invalid"
    Then I should see:
      """
      {"statusCode":601,"answer":"Sorry, your authentication data was incorrect. Please check your nickname and password!","preHeaderMessages":"  \n"}
      """
    And the response code should be "403"

