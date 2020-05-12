@web
Feature: The Top App bar should provide additional functionality to the website

  Scenario: The default top bar should contain a button to open the side bar
    Given I am on "/app"
    And I wait for the page to be loaded
    Then the element "#sidebar" should not be visible
    Then the element "#top-app-bar__default" should be visible
    And  the element "#top-app-bar__btn-sidebar-toggle" should be visible
    When I click "#top-app-bar__btn-sidebar-toggle"
    Then the element "#sidebar" should be visible

  Scenario: The default top bar should contain a link to the index page
    Given I am on "/app/login"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__title" should be visible
    When I click "#top-app-bar__title"
    Then I should be on "/app/"

  Scenario: The default top bar should contain a search button to open the search bar
    Given I am on "/app"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-search" should be visible
    When I click "#top-app-bar__btn-search"
    Then the element "#top-app-bar__search" should be visible
