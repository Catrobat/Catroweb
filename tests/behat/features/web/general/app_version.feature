@web @debug
Feature: At the bottom of every page there should be an to the user invisible version number

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    # And the the app version must be defined in .env.test as TEST_VERSION!

  Scenario: version number should be on every page and themes
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/login/"
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/register/"
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/help/"
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/project/1"
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/user/1"
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "luna"
    And I wait for the page to be loaded
    And the "#app-version" element should contain "TEST_VERSION"
    Then the element "#app-version" should exist
    And I wait for the page to be loaded
    And the "#app-version" element should contain "TEST_VERSION"

  Scenario: version number should not be visible to the user
    Given I am on homepage
    And I wait for the page to be loaded
    Then the element "#app-version" should exist
    But the element "#app-version" should not be visible
