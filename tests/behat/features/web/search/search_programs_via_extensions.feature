@web @search
Feature: Searching for programs with extensions

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |
    And there are projects:
      | id | name      | owned by | extensions |
      | 1  | project 1 | Catrobat | Lego,Phiro |
      | 2  | project 2 | Catrobat | Lego,Drone |
      | 3  | project 3 | User1    | Drone      |
    And I wait 1000 milliseconds

  Scenario: Searching other programs with the same extensions
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I should see "project 1"
    And I should see "Lego"
    And I should see "Phiro"
    When I press on the extension "Lego"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    Then I should see "project 1"
    And I should see "project 2"
    And I should not see "project 3"

  Scenario: search for programs should work
    When I am on "/app/search/Lego"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    And I should see "project 1"
    And I should see "project 2"
    And I should not see "project 3"
