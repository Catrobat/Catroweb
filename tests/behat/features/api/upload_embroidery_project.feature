@api @upload @tag
Feature: Upload a program with tag

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
    Given there are tags:
      | id | en         | de             |
      | 1  | Games      | Spiele         |
      | 2  | Story      | Geschichte     |
      | 3  | Music      | Musik          |
      | 3  | Embroidery | Embroidery     |

  Scenario: upload a tagged program with tags Games and Story on an english device
    Given I have an embroidery project
    And I use the "english" app
    When I upload this program
    Then the program should be tagged with "Embroidery" in the database

