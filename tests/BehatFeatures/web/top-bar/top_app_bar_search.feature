@web @search
Feature: Searching should be possible using a specific Search Top App Bar

  Scenario: The default top bar should contain a search button, but not the search form
    Given I am on "/app"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-search" should be visible
    Then the element "#top-app-bar__search" should not be visible
    Then the element "#top-app-bar__search-form" should not be visible

  Scenario: pressing the search icon should spawn the search bar instead of the default top bar
    Given I am on "/app"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    Then the element "#top-app-bar__search" should not be visible
    And I click the currently visible search icon
    Then the element "#top-app-bar__default" should not be visible
    Then the element "#top-app-bar__btn-search" should not be visible
    And the element "#top-app-bar__search" should be visible
    And the element "#top-app-bar__btn-search-back" should be visible
    And the element "#top-app-bar__search-form" should be visible
    And the element "#top-app-bar__search-input" should be visible
    And the element "#top-app-bar__btn-search-clear" should not be visible

  Scenario: The search label should only use the alt value as content when there is no query defined
    Given I am on "/app"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    Then the element "#top-app-bar__search-label" should have a attribute "alt" with value "Search"
    # Mink is not supporting pseudo elements, we can't test any further here

  Scenario: pressing the search back icon should close the search bar
    Given I am on "/app"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    And the element "#top-app-bar__search" should be visible
    When I click "#top-app-bar__btn-search-back"
    Then the element "#top-app-bar__search" should not be visible
    And the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-search" should be visible

  Scenario: the search header must be active per default on search pages
    Given I am on "/app/search/myQuery"
    And I wait for the page to be loaded
    And the element "#top-app-bar__default" should not be visible
    And the element "#top-app-bar__search" should be visible

  Scenario: the search header should contain the previous query
    Given I am on "/app/search/myLastQuery"
    Then the element "#top-app-bar__default" should not be visible
    And the element "#top-app-bar__search" should be visible
    And the "top-app-bar__search-input" field should contain "myLastQuery"
    And the element "#top-app-bar__btn-search-clear" should be visible

  Scenario: pressing the search back icon after a search should return to the page url before the search
    Given I am on "/app/login"
    And I wait for the page to be loaded
    Then I click "#top-app-bar__btn-search"
    And I wait 500 milliseconds
    And I enter "search1" into visible "#top-app-bar__search-input"
    And I wait 500 milliseconds
    And I press enter in the search bar
    And I wait for the page to be loaded
    Then I should be on "/app/search/search1"
    And I enter "search2" into visible "#top-app-bar__search-input"
    And I press enter in the search bar
    And I wait for the page to be loaded
    Then I should be on "/app/search/search2"
    When I click "#top-app-bar__btn-search-back"
    And I should be on "/app/login"

  Scenario: clear search button should only be visible when search input contains text
    Given I am on "/app"
    And I wait for the page to be loaded
    And I click the currently visible search icon
    And the element "#top-app-bar__search" should be visible
    And the element "#top-app-bar__btn-search-clear" should not be visible
    And I enter "MySearchQuery" into visible "#top-app-bar__search-input"
    And the element "#top-app-bar__btn-search-clear" should be visible

  Scenario: Using the X button one should be able to clear the search
    Given I am on "/app"
    And I wait for the page to be loaded
    And the "top-app-bar__search-input" field should contain ""
    And I click "#top-app-bar__btn-search"
    And the element "#top-app-bar__search" should be visible
    When I enter "MySearchQuery" into visible "#top-app-bar__search-input"
    Then the "top-app-bar__search-input" field should contain "MySearchQuery"
    When I click "#top-app-bar__btn-search-clear"
    Then the "top-app-bar__search-input" field should contain ""

  Scenario: Search bar should stay active between consecutive searches and keep the old query in the search bar
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are projects:
      | id | name                | owned by |
      | 1  | project 1           | User1    |
      | 2  | test project        | User1    |
      | 3  | Test advanced app   | Catrobat |
      | 4  | Catrobat            | User1    |
      | 5  | project 3           | Catrobat |
      | 6  | test advanced games | User1    |
      | 7  | test                | Catrobat |
      | 8  | project test        | User1    |
    Given I am on "/app"
    And I wait for the page to be loaded
    Then I click "#top-app-bar__btn-search"
    And I enter "project" into visible "#top-app-bar__search-input"
    And I press enter in the search bar
    And I wait for the page to be loaded
    Then I should be on "/app/search/project"
    Then I should see "Search results"
    And the element "#top-app-bar__btn-search-back" should be visible
    And the element "#top-app-bar__search-form" should be visible
    And the element "#top-app-bar__search-input" should be visible
    And the element "#top-app-bar__btn-search-clear" should be visible
    Then the "top-app-bar__search-input" field should contain "project"
    Then I enter "Test advanced" into visible "#top-app-bar__search-input"
    And I press enter in the search bar
    And I wait for the page to be loaded
    Then I should be on "/app/search/Test%20advanced"
    Then I should see "Search results"
    And the element "#top-app-bar__btn-search-back" should be visible
    And the element "#top-app-bar__search-form" should be visible
    And the element "#top-app-bar__search-input" should be visible
    And the element "#top-app-bar__btn-search-clear" should be visible
    Then the "top-app-bar__search-input" field should contain "Test advanced"
