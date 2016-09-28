@homepage
Feature: Creating click statistics by clicking on tags, extensions and recommendet programs

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |
    And there are extensions:
      | id | name         | prefix    |
      | 1  | Arduino      | ARDUINO   |
      | 2  | Drone        | DRONE     |
      | 3  | Lego         | LEGO      |
      | 4  | Phiro        | PHIRO     |
      | 5  | Raspberry Pi | RASPI     |
    And there are tags:
      | id | en           | de          |
      | 1  | Game         | Spiel       |
      | 2  | Animation    | Animation   |
      | 3  | Story        | Geschichte  |
      | 4  | Music        | Musik       |
      | 5  | Art          | Kunst       |
      | 6  | Experimental | Experimental|
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | extensions | tags_id |
      | 1  | Minions   | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | Lego,Phiro | 1,2,3,4 |
      | 2  | Galaxy    | p2          | Catrobat | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | Lego,Drone | 1,2,3   |
      | 3  | Alone     | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |            | 1,2     |
      | 4  | Trolol    | p5          | Catrobat | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   | Lego       | 5       |
      | 5  | Nothing   | p6          | Catrobat | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |            | 6       |

  @javascript
  Scenario: Create one statistic entry from tags
    Given I am on "/pocketcode/program/1"
    When I press on the tag "Game"
    And I wait for AJAX to finish
    Then There should be one database entry with type is "tags" and "tag_id" is "1"
    And I should see "Your search returned 3 results"

  @javascript
  Scenario: Create one statistic entry from extensions
    Given I am on "/pocketcode/program/1"
    When I press on the extension "Lego"
    And I wait for AJAX to finish
    Then There should be one database entry with type is "extensions" and "extension_id" is "3"
    And I should see "Your search returned 3 results"

  @javascript
  Scenario: Create one statistic entry from programs
    Given I am on "/pocketcode/program/1"
    When I click on the first recommended program
    And I wait for AJAX to finish
    Then There should be one database entry with type is "programs" and "program_id" is "2"
    And I should see "p2"
