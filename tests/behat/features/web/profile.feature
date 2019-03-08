@homepage
Feature: As a visitor I want to see user profiles

  Background:
    Given there are users:
      | name      | password | token      | email               |
      | Christian | 123456   | cccccccccc | dev1@pocketcode.org |
      | Gregor    | 654321   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description        | owned by  | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1                 | Christian | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 | abcef              | Christian | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 | mein Super Program | Gregor    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |


  Scenario: Calling the profile route without an id should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/pocketcode/profile"
    Then I should see "My Profile"
    When I am on "/pocketcode/profile/"
    Then I should see "My Profile"

  Scenario: Calling the profile route with the id 0 should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/pocketcode/profile/0"
    Then I should see "My Profile"

  Scenario: Calling the profile route with my user id id should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/pocketcode/profile/1"
    Then I should see "My Profile"

  Scenario: Calling the profile route with another id should bring me to the users profile
    Given I log in as "Christian" with the password "123456"
    And I am on "/pocketcode/profile/2"
    Then I should see "Gregor"

  Scenario: Trying to get to myProfile when not logged in should bring me to log in page
    Given I am on "/pocketcode/profile"
    Then I should be on "/pocketcode/login"

  Scenario: Show Christian's profile
    Given I am on "/pocketcode/profile/1"
    Then I should see "Christian"
    And I should see "Amount of programs: 2"
    And I should see "Country: Austria"
    And I should see "Programs of Christian"
    And I should see "program 1"
    And I should see "program 2"
    But I should not see "Gregor"
    And I should not see "program 3"

  Scenario: Show Gregor's profile
    Given I am on "/pocketcode/profile/2"
    Then I should see "Gregor"
    And I should see "Amount of programs: 1"
    And I should see "Country: Austria"
    And I should see "Programs of Gregor"
    And I should see "program 3"
    But I should not see "Christian"
    And I should not see "program 1"
    And I should not see "program 2"