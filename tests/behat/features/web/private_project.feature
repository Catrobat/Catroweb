@homepage
Feature: As a visitor I want to see a program page

  Background:
    Given there are users:
      | name           | password | token      | email               | id |
      | myUsername     | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | randomUsername | 123456   | cccccccccc | dev2@pocketcode.org |  2 |
    And there are programs:
      | id | name      | description | owned by   | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready | private |
      | 1  | project 1 | ......      | myUsername | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      | 0       |
      | 2  | project 2 | ......      | myUsername | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      | 0       |
      | 3  | project 3 | ......      | myUsername | 0         | 0             | 1     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      | 1       |

  Scenario: Private projects should not be visible on the homepage
    Given I am on "/"
    Then I should see "project 1"
    And I should see "project 2"
    And I should not see "project 3"

  Scenario: Your own private projects should always be visible to you
    Given I am on "/app/login"
    And I fill in "username" with "myUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/app/project/1"
    Then I should see "project 1"
    When I am on "/app/project/2"
    Then I should see "project 2"
    When I am on "/app/project/3"
    Then I should see "project 3"

  Scenario: Private projects should be accessible when not logged in over the link
    When I am on "/app/project/1"
    Then I should see "project 1"
    When I am on "/app/project/2"
    Then I should see "project 2"
    When I am on "/app/project/3"
    Then I should see "project 3"

  Scenario: Private projects from a different user should be accessible to you
    Given I am on "/app/login"
    And I fill in "username" with "randomUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/app/project/1"
    Then I should see "project 1"
    When I am on "/app/project/2"
    Then I should see "project 2"
    When I am on "/app/project/3"
    Then I should see "project 3"

  Scenario: MyProfile statistics should count private projects
    Given I am on "/app/login"
    And I fill in "username" with "myUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/app/user/1"
    Then I should see "project 1"
    And I should see "project 2"
    And I should see "project 3"

  Scenario: Profile statistics of a different user should not count private projects
    Given I am on "/app/login"
    And I fill in "username" with "randomUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/app/user/1"
    Then I should see "project 1"
    And I should see "project 2"
    And I should not see "project 3"
    And I should see "Amount of projects: 2"

