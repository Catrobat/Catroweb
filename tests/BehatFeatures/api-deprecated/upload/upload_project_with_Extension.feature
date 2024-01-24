@api @upload @extensions
Feature: Upload a project with extensions

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |

  Scenario: upload a project with extensions
    Given I have a project with arduino, mindstorms and phiro extensions
    And I upload this generated project with id "2", API version 1
    Then the project with id "2" should be marked with "3" extensions in the database

  Scenario: update a project with extensions
    Given I have a project with arduino, mindstorms and phiro extensions
    And I upload this generated project with id "2", API version 1
    When I upload this generated project again without extensions, API version 1
    Then the project with id "2" should be marked with "0" extensions in the database

