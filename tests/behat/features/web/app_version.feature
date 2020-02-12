@homepage
Feature: At the bottom of every page there should be an to the user invisible version number

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
    # And the the app version must be defined in .env.test as TEST_VERSION!

  Scenario: version number should be on every page and themes
    Given I am on homepage
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/login/"
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/register/"
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/help/"
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/project/1"
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "app/user/1"
    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"

    Given I am on "luna"
    And the "#app-version" element should contain "TEST_VERSION"

    Then the element "#app-version" should exist
    And the "#app-version" element should contain "TEST_VERSION"


  Scenario: version number should not be visible to the user
    Given I am on homepage
    Then the element "#app-version" should exist
    And the element "#app-version" should not be visible
