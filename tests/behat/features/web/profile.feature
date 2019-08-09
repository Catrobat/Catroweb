@homepage
Feature: As a visitor I want to see user profiles

  Background:
    Given there are users:
      | name      | password | token      | email               | id |
      | Christian | 123456   | cccccccccc | dev1@pocketcode.org | 1  |
      | Gregor    | 654321   | cccccccccc | dev2@pocketcode.org | 2  |
      | User1     | 654321   | cccccccccc | dev3@pocketcode.org | 3  |

    And there are programs:
      | id  | name       | description        | owned by  | downloads | apk_downloads | views | upload time      | version |
      | 1   | program 1  | p1                 | Christian | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2   | program 2  | abcef              | Christian | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3   | program 3  | mein Super Program | Gregor    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4   | program 4  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5   | program 5  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6   | program 6  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7   | program 7  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8   | program 8  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9   | program 9  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10  | program 10 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11  | program 11 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12  | program 12 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13  | program 13 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14  | program 14 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15  | program 15 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16  | program 16 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17  | program 17 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18  | program 18 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19  | program 19 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20  | program 20 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21  | program 21 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22  | program 22 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 23  | program 23 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 24  | program 24 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 25  | program 25 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 26  | program 26 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 27  | program 27 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 28  | program 28 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |



  Scenario: Calling the profile route without an id should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/profile"
    Then I should see "My Profile"
    When I am on "/app/profile/"
    Then I should see "My Profile"

  Scenario: Calling the profile route with the id 0 should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/profile/0"
    Then I should see "My Profile"

  Scenario: Calling the profile route with my user id id should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/profile/1"
    Then I should see "My Profile"

  Scenario: Calling the profile route with another id should bring me to the users profile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/profile/2"
    Then I should see "Gregor"

  Scenario: Trying to get to myProfile when not logged in should bring me to log in page
    Given I am on "/app/profile"
    Then I should be on "/app/login"

  Scenario: Show Christian's profile
    Given I am on "/app/profile/1"
    Then I should see "Christian"
    And I should see "Amount of programs: 2"
    And I should see "Country: Austria"
    And I should see "Programs of Christian"
    And I should see "program 1"
    And I should see "program 2"
    But I should not see "Gregor"
    And I should not see "program 3"

  Scenario: Show Gregor's profile
    Given I am on "/app/profile/2"
    Then I should see "Gregor"
    And I should see "Amount of programs: 1"
    And I should see "Country: Austria"
    And I should see "Programs of Gregor"
    And I should see "program 3"
    But I should not see "Christian"
    And I should not see "program 1"
    And I should not see "program 2"

  Scenario: at a profile page there should always all programs be visible
    Given I am on "/app/profile/3"
    Then I should see "program 4"
    And I should see "program 5"
    And I should see "program 6"
    And I should see "program 7"
    And I should see "program 8"
    And I should see "program 9"
    And I should see "program 10"
    And I should see "program 11"
    And I should see "program 12"
    And I should see "program 13"
    And I should see "program 14"
    And I should see "program 15"
    And I should see "program 16"
    And I should see "program 17"
    And I should see "program 18"
    And I should see "program 19"
    And I should see "program 20"
    And I should see "program 21"
    And I should see "program 22"
    And I should see "program 23"
    And I should see "program 24"
    And I should see "program 25"
    And I should see "program 26"
    And I should see "program 27"
    And I should see "program 28"
