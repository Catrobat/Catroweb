@api @projects @post
# Scratch is faked: FakeScratchFetcher serves the offline project in tests/TestData/ScratchConverter for the ids in its
# metadata.json. 70058680 and 70058681 are both "Gamma Ray Racer" by scratchcat; 70058682 cannot be converted.
# Rate limiting (429) and the busy converter (503) are covered by ProjectsApiScratchImportTest.
Feature: Import a public Scratch project as a Pocket Code project

  Background:
    Given I have a request header "HTTP_ACCEPT" with value "application/json"

  Scenario: Importing a Scratch project converts it into a downloadable project of the Scratch author
    When I request "POST" "/api/projects/scratch/70058680"
    Then the response code should be "201"
    And the response Header should contain the key "Location"
    And the client response should contain "Gamma Ray Racer"
    And there should be "1" projects in the database
    And there should be "1" users in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 1 by "scratchcat"
    When I download the project converted from Scratch project 70058680
    Then I should receive an application file

  Scenario: Importing an unchanged Scratch project again returns the existing copy
    When I request "POST" "/api/projects/scratch/70058680"
    And I request "POST" "/api/projects/scratch/70058680"
    Then the response code should be "200"
    And there should be "1" projects in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 1 by "scratchcat"

  Scenario: Importing a Scratch project that changed on Scratch converts it again
    When I request "POST" "/api/projects/scratch/70058680"
    And the Scratch project 70058680 was modified on Scratch at "2099-01-01T00:00:00.000Z"
    And I request "POST" "/api/projects/scratch/70058680"
    Then the response code should be "201"
    And there should be "1" projects in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 2 by "scratchcat"

  Scenario: A copy made by an older converter is converted again
    When I request "POST" "/api/projects/scratch/70058680"
    And the project converted from Scratch project 70058680 was made by converter version 1
    And I request "POST" "/api/projects/scratch/70058680"
    Then the response code should be "201"
    And there should be "1" projects in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 2 by "scratchcat"

  Scenario: Importing a Scratch project that Scratch doesn't know fails with 404 and creates nothing
    When I request "POST" "/api/projects/scratch/1"
    Then the response code should be "404"
    And the JSON response field "error.code" should be "404"
    And the client response should contain "does not exist or is not shared"
    And there should be "0" projects in the database
    And there should be "0" users in the database

  Scenario: A Scratch project that cannot be converted fails with 422 and the reason
    When I request "POST" "/api/projects/scratch/70058682"
    Then the response code should be "422"
    And the JSON response field "error.code" should be "422"
    And the client response should contain "made with an older Scratch version"
    And there should be "0" projects in the database

  Scenario: Two Scratch projects with the same title by the same author stay two projects
    When I request "POST" "/api/projects/scratch/70058680"
    And I request "POST" "/api/projects/scratch/70058681"
    Then the response code should be "201"
    And there should be "2" projects in the database
    And there should be "1" users in the database
    And the Scratch project 70058680 should be converted to "Gamma Ray Racer" version 1 by "scratchcat"
    And the Scratch project 70058681 should be converted to "Gamma Ray Racer (70058681)" version 1 by "scratchcat"
