@api
Feature: Get recommended programs

  To find programs with tags and extensions, which are similar to the tags and extensions of one selected program

  Background:
    Given there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |
    And there are tags:
      | id | en           |
      | 1  | Games        |
      | 2  | Animation    |
      | 3  | Story        |
      | 4  | Music        |
      | 5  | Art          |
      | 6  | Experimental |
    And there are programs:
      | id | name    | description | owned by | downloads | views | upload time      | version | extensions | tags_id | debug |
      | 1  | Game    | p4          | Catrobat | 5         | 1     | 01.03.2013 12:00 | 0.8.5   |            | 5       | false |
      | 2  | Minions | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | Lego,Phiro | 1,2,3,4 | false |
      | 3  | Galaxy  | p2          | Catrobat | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | Lego,Drone | 1,2,3   | false |
      | 4  | Alone   | p3          | Catrobat | 5         | 1     | 01.03.2013 12:00 | 0.8.5   |            | 1,2     | false |
      | 5  | Trolol  | p5          | Catrobat | 5         | 1     | 01.03.2013 12:00 | 0.8.5   | Lego       | 5       | true  |


  Scenario Outline: A request must have specific parameters to succeed with the recommender api

    Given I use a <build type> build of the Catroid app
    And I have a parameter "program_id" with value "1"
    And I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/recsys.json" with these parameters
    Then I should get the programs "<programs>"

    Examples:
      | build type | programs |
      | debug      | Trolol   |
      | release    |          |

  Scenario Outline: You get recommended programs which are more similar to the selected program first

    Given I use a <build type> build of the Catroid app
    And I use the limit "10"
    And I use the offset "0"
    When I search similar programs for program id "2"
    Then I should get the programs "<programs>"

    Examples:
      | build type | programs            |
      | debug      | Galaxy,Alone,Trolol |
      | release    | Galaxy,Alone        |
