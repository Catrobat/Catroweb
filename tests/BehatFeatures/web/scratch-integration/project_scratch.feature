@web @search
# Scratch is faked: "there are Scratch projects" seeds what FakeScratchSearch finds, and FakeScratchFetcher serves
# the offline project in tests/TestData/ScratchConverter for the ids in its metadata.json (70058682 cannot be
# converted). Importing itself is covered by api/projects/POST_projects_scratch.
Feature: Open a Scratch project from the search as a Pocket Code project

  Scenario: Clicking a Scratch search result converts the project and opens it
    Given there are Scratch projects:
      | id       | name            | username   |
      | 70058680 | Gamma Ray Racer | scratchcat |
    And I am on "/app/search/Gamma"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#search-scratch-projects .search-card" to be visible
    When I click "#search-scratch-projects button.search-card"
    # The fake conversion still takes a second or two, long enough to see the converting state.
    Then I wait for the element "#search-scratch-projects .search-card__overlay-label" to contain "Converting for Pocket Code"
    And I wait for the element "#share-snackbar-label" to contain "Converting “Gamma Ray Racer”"
    # The conversion runs before the page changes; this step polls for up to 10 s.
    And I wait for the element "#name" to contain "Gamma Ray Racer"
    And the url should match "/app/project/[0-9a-f-]+$"
    And there should be "1" projects in the database

  Scenario: A Scratch search result that cannot be converted shows why and stays on the search page
    Given there are Scratch projects:
      | id       | name               | username   |
      | 70058682 | Old Scratch 2 Game | scratchcat |
    And I am on "/app/search/Old"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#search-scratch-projects .search-card" to be visible
    When I click "#search-scratch-projects button.search-card"
    Then I wait for the element "#share-snackbar-label" to contain "Only Scratch 3 projects can be converted."
    And the url should match "/app/search/Old$"
    And there should be "0" projects in the database
