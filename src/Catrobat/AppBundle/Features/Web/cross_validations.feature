@homepage
Feature: Cross validation for recommendation system

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
      | 1  | Games        | Spiel       |
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

  Scenario: User with selected russian language cant see the recommender
    Given I am on "/pocketcode/program/1"
    And the selected language is "English"
    And I should see "Similar Programs"
    And the element "#recommendations" should be visible
    When I switch the language to "Russisch"
    And I wait 250 milliseconds
    Then I should not see "Similar Programs"
    And I should not see "#recommendations"



