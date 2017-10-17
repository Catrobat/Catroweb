@homepage
Feature: Searching for programs with extensions

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456   | cccccccccc  | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc  | dev2@pocketcode.org |
    And there are extensions:
      | id | name         | prefix    |
      | 1  | Arduino      | ARDUINO   |
      | 2  | Drone        | DRONE     |
      | 3  | Lego         | LEGO      |
      | 4  | Phiro        | PHIRO     |
      | 5  | Raspberry Pi | RASPI     |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version | extensions  |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | Lego,Phiro  |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   | Lego,Drone  |
      | 3  | myprog 3  |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   | Drone       |
    And I am on "/pocketcode"

  Scenario: Searching other programs with the same extensions
    Given I am on "/pocketcode/program/1"
    And I should see "program 1"
    And I should see "Lego"
    And I should see "Phiro"
    When I press on the extension "Lego"
    Then I should see "Your search returned 2 results"
    Then I should see "program 1"
    And I should see "program 2"
    And I should not see "myprog 3"

  Scenario: search for programs should work
    When I am on "/pocketcode/search/Lego"
    Then I should see "Your search returned 2 results"
    And I should see "program 1"
    And I should see "program 2"
    And I should not see "myprog 3"
