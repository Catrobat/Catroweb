@homepage
Feature: As a visitor I want to see a program page

  Background:
    Given there are users:
      | name           | password | token      | email               |
      | myUsername     | 123456   | cccccccccc | dev1@pocketcode.org |
      | randomUsername | 123456   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description | owned by   | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready | private |
      | 1  | program 1 | ......      | myUsername | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      | 0       |
      | 2  | program 2 | ......      | myUsername | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      | 0       |
      | 3  | program 3 | ......      | myUsername | 0         | 0             | 1     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      | 1       |

  Scenario: Private programs should not be visible on the homepage
    Given I am on "/"
    Then I should see "program 1"
    And I should see "program 2"
    And I should not see "program 3"

  Scenario: Your own private programs should always be visible to you
    Given I am on "/pocketcode/login"
    And I fill in "username" with "myUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/pocketcode/program/1"
    Then I should see "program 1"
    When I am on "/pocketcode/program/2"
    Then I should see "program 2"
    When I am on "/pocketcode/program/3"
    Then I should see "program 3"

  Scenario: Private programs should not be accessible when not logged in
    When I am on "/pocketcode/program/1"
    Then I should see "program 1"
    When I am on "/pocketcode/program/2"
    Then I should see "program 2"
    When I am on "/pocketcode/program/3"
    Then I should not see "program 3"

  Scenario: Private programs from a different user should not be accessible to you
    Given I am on "/pocketcode/login"
    And I fill in "username" with "randomUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/pocketcode/program/1"
    Then I should see "program 1"
    When I am on "/pocketcode/program/2"
    Then I should see "program 2"
    When I am on "/pocketcode/program/3"
    Then I should not see "program 3"

  Scenario: MyProfile statistics should count private programs
    Given I am on "/pocketcode/login"
    And I fill in "username" with "myUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/pocketcode/profile/1"
    Then I should see "program 1"
    And I should see "program 2"
    And I should see "program 3"

  Scenario: Profile statistics of a different user should not count private programs
    Given I am on "/pocketcode/login"
    And I fill in "username" with "randomUsername"
    And I fill in "password" with "123456"
    And I press "Login"
    When I am on "/pocketcode/profile/1"
    Then I should see "program 1"
    And I should see "program 2"
    And I should not see "program 3"
    And I should see "Amount of programs: 2"

