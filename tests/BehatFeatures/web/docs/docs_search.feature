@web @docs
Feature: The search (top bar) finds the user docs

  # The test environment's embedder (FakeDocsEmbedder) gives "jump" and "y" one meaning, so the hybrid search
  # finds the "Change Y by" brick for "make my cat jump" although they have no word in common.

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name             | owned by |
      | 1  | my first project | Catrobat |
    And I wait for the search index to be updated
    And the docs are synced from the test docs folder
    And the docs search index is up-to-date

  Scenario: Searching from the top bar finds a docs page by its title, next to projects
    Given I am on "/app"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-search"
    And I enter "first project" into visible "#top-app-bar__search-input"
    And I press enter in the search bar
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should be on "/app/search/first%20project"
    And I wait for the element "#search-docs-guides" to contain "Your first project"
    And the element "#search-projects" should contain "my first project"
    And the element "#search-results .search-section:not(.d-none)" should contain "my first project"

  Scenario: A German title, for a visitor who reads German
    Given I am on "/app"
    And I set the cookie "hl" to "de_DE"
    And I am on "/app/search/Katze%20springen"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#search-docs-guides" to contain "Lass deine Katze springen"

  Scenario: A paraphrased question finds the matching brick
    Given I am on "/app/search/make%20my%20cat%20jump"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#search-docs-bricks" to contain "Change Y by"
    And the element "#search-docs-guides" should contain "Make your cat jump"

  Scenario: A search started on a docs page shows the docs results first
    Given I am on "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-search"
    And I enter "first project" into visible "#top-app-bar__search-input"
    And I press enter in the search bar
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I wait for the element "#search-docs-guides" to contain "Your first project"
    And the element "#search-results .search-section:not(.d-none)" should contain "Your first project"
