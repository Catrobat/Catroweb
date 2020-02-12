@homepage
Feature: As a visitor I want to see a program page

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name             | owned by | private |
      | 1  | public project 1 | Catrobat | 0       |
      | 2  | public project 2 | Catrobat | 0       |
      | 3  | private project  | Catrobat | 1       |

  Scenario: Private projects should not be visible on the homepage
    Given I am on the homepage
    And I wait for the page to be loaded
    Then I should see "public project 1"
    And I should see "public project 2"
    But I should not see "private project"

  Scenario: Your own projects should always be visible to you, not matter if private or not
    Given I am log in as "Catrobat"
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "public project 1"
    When I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "public project 2"
    When I am on "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "private project"

  Scenario: Private projects should be accessible when not logged in over the link
    When I go to "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "private project"

  Scenario: Private projects from a different user should be accessible to you over the link
    Given I am log in as "OtherUser"
    When I go to "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "private project"

  Scenario: I should see my private projects
    Given I am log in as "Catrobat"
    When I go to "/app/user/1"
    And I wait for the page to be loaded
    Then I should see "public project 1"
    And I should see "public project 2"
    And I should see "private project"

  Scenario: Other users should not see my private projects
    Given I am log in as "OtherUser"
    When I go to "/app/user/1"
    And I wait for the page to be loaded
    Then I should see "public project 1"
    And I should see "public project 2"
    And I should not see "private project"
    And I should see "Amount of projects: 2"

