@api
Feature: Get the random projects

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
      | User2    | vwxyz    | aaaaaaaaaa | 3  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible |
      | 1  | project 1 | p1          | Catrobat | 1         | 4     | 01.01.2013 12:00 | 0.8.5   | true    |
      | 2  | project 2 | p2          | Catrobat | 2         | 3     | 01.02.2013 13:00 | 0.8.5   | false   |
      | 3  | project 3 | p3          | User1    | 3         | 2     | 01.03.2012 14:00 | 0.8.5   | false   |
      | 4  | project 4 | p4          | User2    | 4         | 1     | 01.04.2012 15:00 | 0.8.5   | true    |
    And the current time is "01.08.2014 13:00"

  Scenario: show random projects
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/randomProjects.json" with these parameters
    Then I should get 2 projects in random order:
      | Name      |
      | project 1 |
      | project 4 |

  Scenario: show random project with offset
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/randomProjects.json" with these parameters
    Then I should get 1 projects in random order:
      | Name      |
      | project 1 |
      | project 4 |
    And I have a parameter "offset" with value "1"
    When I GET "/app/api/projects/randomProjects.json" with these parameters
    Then I should get 1 projects in random order:
      | Name      |
      | project 1 |
      | project 4 |
    And I have a parameter "offset" with value "2"
    When I GET "/app/api/projects/randomProjects.json" with these parameters
    Then I should get 0 projects in random order:
      | Name      |
      | project 1 |
      | project 4 |