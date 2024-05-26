@web @extension
Feature: Extension are inserted/update to the database by a symfony command

  Background:
    Given there should be "0" extensions in the database

  Scenario: Running the command adds all extension to the database
    Given I run the update extensions command
    Then there should be "7" extensions in the database

  Scenario: Rerunning the command just overwrites the old entries
    Given I run the update extensions command
    Then there should be "7" extensions in the database
    When I run the update extensions command
    Then there should be "7" extensions in the database

  Scenario: Rerunning the command must keep all project extensions
    Given there are users:
      | id | name     |
      | 1  | Achiever |
      | 2  | Catrobat |
    And I run the update extensions command
    Then there should be "7" extensions in the database
    And there are projects:
      | id | name    | owned by | extensions            |
      | 1  | Minions | Catrobat | embroidery,mindstorms |
    Then the project with name "Minions" should have 2 extensions
    When I run the update extensions command
    Then there should be "7" extensions in the database
    And the project with name "Minions" should have 2 extensions