@api @upload @tag
Feature: Upload a program with tag

  Background:
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

