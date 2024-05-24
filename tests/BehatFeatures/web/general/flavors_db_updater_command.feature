@web @tags
Feature: Flavors are inserted/update to the database by a symfony command

  Background:
    Given there should be "0" flavors in the database

  Scenario: Running the command adds all flavors to the database
    Given I run the update flavors command
    Then there should be "9" flavors in the database

  Scenario: Rerunning the command just overwrites the old entries
    Given I run the update flavors command
    Then there should be "9" flavors in the database
    When I run the update flavors command
    Then there should be "9" flavors in the database
