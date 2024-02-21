@api
Feature: Report a project

  Background:
    Given there are users:
      | name     | password | token      | id | upload_token |
      | Catrobat | 12345    | cccccccccc | 1  | secret       |
      | User1    | vwxyz    | aaaaaaaaaa | 2  | Abc123       |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: Report project over old API not possible without being logged in
    Given I have a parameter "project" with value "1"
    And I have a parameter "category" with value "spam"
    And I have a parameter "note" with value "Bad Project"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then the response status code should be "401"

  Scenario: report project with invalid project
    Given I have a parameter "project" with value "4"
    And I have a parameter "category" with value "Bad Project"
    And I have a parameter "note" with value "Bad Project"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then I should get the json object:
      """
      {"statusCode":506,"answer":"Invalid project.","preHeaderMessages":""}
      """

  Scenario: report project with missing parameter
    Given I have a parameter "project" with value ""
    And I have a parameter "category" with value ""
    And I have a parameter "note" with value "Bad project"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then I should get the json object:
    """
      {"statusCode":501,"answer":"POST-data not correct or missing!","preHeaderMessages":""}
    """

  Scenario: Report project over old API requires upload_token
    Given I have a parameter "project" with value "1"
    And I have a parameter "category" with value "spam"
    And I have a parameter "note" with value "Bad Project"
    And I use a valid upload token for "Catrobat"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then the response status code should be "200"

  Scenario: Report project already supports new tokens
    Given I have a parameter "project" with value "1"
    And I have a parameter "category" with value "spam"
    And I have a parameter "note" with value "Bad Project"
    And I use a valid JWT Bearer token for "Catrobat"
    When I POST these parameters to "/app/api/reportProject/reportProject.json"
    Then the response status code should be "200"