@api
Feature: Search extensions projects

  To find projects, users should be able to search all available projects for specific words and extensions

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    And there are projects:
      | id | name    | description | owned by | downloads | views | upload time      | version | extensions       |
      | 1  | Minions | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | mindstorms,phiro |
      | 2  | Galaxy  | p2          | User1    | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | mindstorms,drone |
      | 3  | Alone   | p3          | User1    | 5         | 1     | 01.03.2013 12:00 | 0.8.5   |                  |


  Scenario: A request must have specific parameters to succeed with the extension search

    Given I have a parameter "q" with value "phiro"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search/extensionProjects.json" with these parameters
    Then I should get following projects:
      | name    |
      | Minions |


  Scenario: Search more projects with the same extension over the normal search

    Given I use the limit "10"
    And I use the offset "0"
    When I search for "mindstorms"
    Then I should get following projects:
      | name    |
      | Galaxy  |
      | Minions |
