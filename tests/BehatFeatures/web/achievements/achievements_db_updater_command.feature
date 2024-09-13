@web @achievements
Feature: Achievements are inserted/update to the database by a symfony command

  Background:
    Given there should be "0" achievements in the database

  Scenario: Running the command adds all achievements to the database
    Given I run the update achievements command
    Then there should be "11" achievements in the database

  Scenario: Rerunning the command just overwrites the old entries
    Given I run the update achievements command
    Then there should be "11" achievements in the database
    When I run the update achievements command
    Then there should be "11" achievements in the database

  Scenario: Rerunning the command must keep all user achievements
    Given there are users:
      | id | name     |
      | 1  | Achiever |
      | 2  | Catrobat |
    And I run the update achievements command
    Then there should be "11" achievements in the database
    And there are user achievements:
      | id | user     | achievement | seen_at | unlocked_at |
      | 1  | Catrobat | bronze_user |         | 2021-03-03  |
      | 2  | Achiever | silver_user |         | 2021-05-05  |
    Then there should be "2" user achievements in the database
    When I run the update achievements command
    Then there should be "11" achievements in the database
    And there should be "2" user achievements in the database
