@homepage
Feature: Showing similar programs on details page of one program

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
    And there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |
    And there are tags:
      | id | en           | de           |
      | 1  | Games        | Spiel        |
      | 2  | Animation    | Animation    |
      | 3  | Story        | Geschichte   |
      | 4  | Music        | Musik        |
      | 5  | Art          | Kunst        |
      | 6  | Experimental | Experimental |
    And there are programs:
      | id | name    | description | owned by | downloads | apk_downloads | views | upload time      | version | extensions | tags_id |
      | 1  | Minions | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | Lego,Phiro | 1,2,3,4 |
      | 2  | Galaxy  | p2          | Catrobat | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | Lego,Drone | 1,2,3   |
      | 3  | Alone   | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |            | 1,2     |
      | 4  | Trolol  | p5          | Catrobat | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   | Lego       | 5       |
      | 5  | Nothing | p6          | Catrobat | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |            | 6       |
    And I am on "/pocketcode"

  Scenario: Showing similar programs
    When I go to "/pocketcode/program/1"
    Then I should see "Minions"
    And I should see "Similar Programs"
    And I should see "Galaxy"
    And I should see "Alone"
    And I should see "Trolol"
    But I should not see "Nothing"

  Scenario: No similar programs are given
    When I go to "/pocketcode/program/5"
    Then I should see "Nothing"
    And I should not see "Art"
    And I should not see "Similar Programs"
    And I should not see "Trolol"
    And I should not see "Minions"
    And I should not see "Galaxy"
    And I should not see "Alone"
