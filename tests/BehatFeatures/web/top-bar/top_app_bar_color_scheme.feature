@web
Feature: The color scheme options (Light, Dark, Auto) should be available in the overflow menu.

  Background:
    Given I am on "/app"
    And I wait for the page to be loaded

  Scenario: The overflow menu contains Light, Dark, and Auto color scheme options
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    Then the element ".theme-picker [data-theme-value='light']" should be visible
    And the element ".theme-picker [data-theme-value='dark']" should be visible
    And the element ".theme-picker [data-theme-value='auto']" should be visible

  Scenario: The old standalone color scheme toggle does not exist
    Then the element "#color-scheme-switch" should not exist

  Scenario: Clicking Dark mode activates the dark option
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    And I click ".theme-picker [data-theme-value='dark']"
    And I click ".swal2-confirm"
    And I wait 500 milliseconds
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    Then the element ".theme-picker [data-theme-value='dark']" should have a attribute "aria-checked" with value "true"
    And the element ".theme-picker [data-theme-value='light']" should have a attribute "aria-checked" with value "false"
    And the element ".theme-picker [data-theme-value='auto']" should have a attribute "aria-checked" with value "false"

  Scenario: Clicking Light mode activates the light option
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    And I click ".theme-picker [data-theme-value='light']"
    And I click ".swal2-confirm"
    And I wait 500 milliseconds
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    Then the element ".theme-picker [data-theme-value='light']" should have a attribute "aria-checked" with value "true"
    And the element ".theme-picker [data-theme-value='dark']" should have a attribute "aria-checked" with value "false"

  Scenario: Clicking Auto mode activates the auto option
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    And I click ".theme-picker [data-theme-value='auto']"
    And I click ".swal2-confirm"
    And I wait 500 milliseconds
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    Then the element ".theme-picker [data-theme-value='auto']" should have a attribute "aria-checked" with value "true"
    And the element ".theme-picker [data-theme-value='dark']" should have a attribute "aria-checked" with value "false"
    And the element ".theme-picker [data-theme-value='light']" should have a attribute "aria-checked" with value "false"

  Scenario: Color scheme options are also available on project pages
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-options"
    And I wait 500 milliseconds
    And I click "#top-app-bar__btn-color-scheme"
    And I wait 500 milliseconds
    Then the element ".theme-picker [data-theme-value='light']" should be visible
    And the element ".theme-picker [data-theme-value='dark']" should be visible
    And the element ".theme-picker [data-theme-value='auto']" should be visible
