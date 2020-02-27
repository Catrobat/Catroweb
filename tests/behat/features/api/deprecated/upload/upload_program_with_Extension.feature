@api @upload @extensions
Feature: Upload a program with extensions

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc |  1 |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
    And there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |

  Scenario: upload a program with extensions
    Given I have a program with Arduino, Lego and Phiro extensions
    When I upload this program
    Then the program should be marked with extensions in the database

  Scenario: update a program with extensions
    Given I have a program with Arduino, Lego and Phiro extensions
    And I upload this program with id "2"
    When I upload the program again without extensions
    Then the program with id "2" should be marked with no extensions in the database

