# Missing in new API - To be fixed with ticket: SHARE-367

@web @recommendations @disabled
Feature: Similar project

  Background:
    Given there are users:
      | name     | id |
      | Catrobat | 1  |
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
    And there are projects:
      | id | name    | description | owned by | downloads | apk_downloads | views | upload time      | version | extensions | tags_id |
      | 1  | Minions | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | Lego,Phiro | 1,2,3,4 |
      | 2  | Galaxy  | p2          | Catrobat | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   | Lego,Drone | 1,2,3   |
      | 3  | Alone   | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |            | 1,2     |
      | 4  | Trolol  | p5          | Catrobat | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   | Lego       | 5       |
      | 5  | Nothing | p6          | Catrobat | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |            | 6       |

  Scenario: Project pages have recommendations about similar projects
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Similar Projects"
    And the element "#recommendations" should be visible
    And I should see 3 "#recommendations .program"

