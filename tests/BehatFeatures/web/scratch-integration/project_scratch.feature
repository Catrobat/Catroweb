@project_page

# Scratch is faked: FakeScratchFetcher serves the offline project in tests/TestData/ScratchConverter for the ids in
# its metadata.json (70058680 and 70058681, both "Gamma Ray Racer" by scratchcat). Other ids don't exist.
Feature: As a visitor I want to open Scratch projects as Pocket Code projects

  Scenario: Opening a Scratch project converts it into a downloadable project of the Scratch author
    When I request "GET" "/app/scratch/project/70058680"
    Then I should be redirected to a catrobat project
    And there should be "1" projects in the database
    And there should be "1" users in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 1 by "scratchcat"
    When I download the project converted from Scratch project 70058680
    Then I should receive an application file

  Scenario: Opening an unchanged Scratch project again does not convert it again
    When I request "GET" "/app/scratch/project/70058680"
    And I request "GET" "/app/scratch/project/70058680"
    Then I should be redirected to a catrobat project
    And there should be "1" projects in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 1 by "scratchcat"

  Scenario: Opening a Scratch project that changed on Scratch converts it again
    When I request "GET" "/app/scratch/project/70058680"
    And the Scratch project 70058680 was modified on Scratch at "2099-01-01T00:00:00.000Z"
    And I request "GET" "/app/scratch/project/70058680"
    Then I should be redirected to a catrobat project
    And there should be "1" projects in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 2 by "scratchcat"

  Scenario: Opening an unknown Scratch project shows an error and creates nothing
    Given I am on "/app/scratch/project/1"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    And I wait for the element "#share-snackbar-label" to contain "does not exist"
    And there should be "0" projects in the database
    And there should be "0" users in the database

  Scenario: Two Scratch projects with the same title by the same author stay two projects
    When I request "GET" "/app/scratch/project/70058680"
    And I request "GET" "/app/scratch/project/70058681"
    Then I should be redirected to a catrobat project
    And there should be "2" projects in the database
    And there should be "1" users in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 1 by "scratchcat"
    And the Scratch project 70058681 should be converted to "Gamma Ray Racer (70058681)" version 1 by "scratchcat"
