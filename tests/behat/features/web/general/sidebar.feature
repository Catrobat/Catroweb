@homepage
Feature: Sidebar Navigation

  Scenario: Button opens and closes sidebar
    Given I am on the homepage
    Then the element "#sidebar" should not be visible
    And the element "#sidebar-overlay" should not be visible
    When I click "#top-app-bar__btn-sidebar-toggle"
    Then the url should match "/app/$"
    And the element "#sidebar" should be visible
    And the element "#sidebar-overlay" should be visible
    When I click "#top-app-bar__btn-sidebar-toggle"
    Then the url should match "/app/$"
    And the element "#sidebar" should not be visible
    And the element "#sidebar-overlay" should not be visible

  Scenario: Back button closes sidebar
    Given I am on the homepage
    And I open the menu
    And I click "#btn-login"
    And I click "#top-app-bar__btn-sidebar-toggle"
    Then the url should match "/app/login$"
    And the element "#sidebar" should be visible
    And the element "#sidebar-overlay" should be visible
    When I click browser's back button
    Then the url should match "/app/login"
    And I wait for the page to be loaded
    And the element "#sidebar" should not be visible
    And the element "#sidebar-overlay" should not be visible
