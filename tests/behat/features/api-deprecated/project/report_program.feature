@api
Feature: Report a program

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc |  1 |
      | User1    | vwxyz    | aaaaaaaaaa |  2 |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: Report project over old API not possible
    Given I have a parameter "program" with value "1"
    And I have a parameter "category" with value "spam"
    And I have a parameter "note" with value "Bad Project"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then the response status code should be "401"

  Scenario: report program with invalid project
    Given I have a parameter "program" with value "4"
    And I have a parameter "category" with value "Bad Project"
    And I have a parameter "note" with value "Bad Project"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then I should get the json object:
      """
      {"statusCode":506,"answer":"Invalid project.","preHeaderMessages":""}
      """

  Scenario: report program with missing parameter
    Given I have a parameter "program" with value ""
    And I have a parameter "category" with value ""
    And I have a parameter "note" with value "Bad project"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then I should get the json object:
    """
      {"statusCode":501,"answer":"POST-data not correct or missing!","preHeaderMessages":""}
    """
