@api @extensions
Feature: Upload a program with extensions

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | RemixOf  |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | null     |
    And there are extensions:
      | id | name         | prefix    |
      | 1  | Arduino      | ARDUINO   |
      | 2  | Drone        | DRONE     |
      | 3  | Lego         | LEGO      |
      | 4  | Phiro        | PHIRO     |
      | 5  | Raspberry Pi | RASPI     |


  Scenario: upload a program with extensions
    Given I have a program with Arduino, Lego and Phiro extensions
    When I upload this program
    Then the program should be marked with extensions in the database

  Scenario: update a program with extensions
    Given I have a program with Arduino, Lego and Phiro extensions
    And I upload this program
    When I upload the program again without extensions
    Then the program should be marked with no extensions in the database

