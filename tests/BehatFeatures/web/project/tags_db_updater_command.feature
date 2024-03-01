@web @tags
Feature: Tags are inserted/update to the database by a symfony command

  Background:
    Given there should be "0" tags in the database

  Scenario: Running the command adds all tags to the database
    Given I run the update tags command
    Then there should be "8" tags in the database

  Scenario: Rerunning the command just overwrites the old entries
    Given I run the update tags command
    Then there should be "8" tags in the database
    When I run the update tags command
    Then there should be "8" tags in the database

  Scenario: Rerunning the command must keep all project tags
    Given there are users:
      | id | name     |
      | 1  | Achiever |
      | 2  | Catrobat |
    And I run the update tags command
    Then there should be "8" tags in the database
    And there are projects:
      | id | name    | owned by | tags            |
      | 1  | Minions | Catrobat | game, animation |
    Then the project with name "Minions" should have 2 tags
    When I run the update tags command
    Then there should be "8" tags in the database
    And the project with name "Minions" should have 2 tags