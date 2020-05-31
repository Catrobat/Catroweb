@api @projects @post @tag
Feature: Upload a program with tag

  Background:
    Given there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |

  Scenario: uploading a embroidery program should add the embroidery extension to the database and
  also adding the extension to the program
    Given I have an embroidery project
    And I use the "english" app, API version 2
    When I upload this generated program, API version 2
    Then the embroidery program should have the "Embroidery" extension

  Scenario: uploading a embroidery program should add the embroidery to the program
    Given I have an embroidery project
    And there are extensions:
      | id | name         | prefix       |
      | 1  | newOne       | NEW1         |
      | 2  | Embroidery   | Embroidery   |
    And I use the "english" app, API version 2
    When I upload this generated program, API version 2
    Then the embroidery program should have the "Embroidery" extension

  Scenario: uploading a normal program should must not add the embroidery extension to the program
    Given I have a program
    And I use the "english" app, API version 2
    When I upload this generated program, API version 2
    Then the project should have no extension
