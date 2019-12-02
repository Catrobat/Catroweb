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
      | 1   | project 1  | p1                 | Christian | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2   | project 2  | abcef              | Christian | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3   | project 3  | mein Super project | Gregor    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4   | project 4  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5   | project 5  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6   | project 6  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 7   | project 7  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 8   | project 8  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9   | project 9  |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10  | project 10 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 11  | project 11 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 12  | project 12 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 13  | project 13 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 14  | project 14 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 15  | project 15 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 16  | project 16 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 17  | project 17 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 18  | project 18 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 19  | project 19 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 20  | project 20 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 21  | project 21 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 22  | project 22 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 23  | project 23 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 24  | project 24 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 25  | project 25 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 26  | project 26 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 27  | project 27 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 28  | project 28 |                    | User1     | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: Calling the profile route without an id should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/user"
    Then I should see "My Profile"
    When I am on "/app/user/"
    Then I should see "My Profile"

  Scenario: Calling the profile route with the id 0 should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/user/0"
    Then I should see "My Profile"

  Scenario: Calling the profile route with my user id id should bring me to myProfile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/user/1"
    Then I should see "My Profile"

  Scenario: Calling the profile route with another id should bring me to the users profile
    Given I log in as "Christian" with the password "123456"
    And I am on "/app/user/2"
    Then I should see "Gregor"

  Scenario: Trying to get to myProfile when not logged in should bring me to log in page
    Given I am on "/app/user"
    Then I should be on "/app/login"

  Scenario: Show Christian's profile
    Given I am on "/app/user/1"
    Then I should see "Christian"
    And I should see "Amount of projects: 2"
    And I should see "Country: Austria"
    And I should see "Projects of Christian"
    And I should see "project 1"
    And I should see "project 2"
    But I should not see "Gregor"
    And I should not see "project 3"

  Scenario: Show Gregor's profile
    Given I am on "/app/user/2"
    Then I should see "Gregor"
    And I should see "Amount of projects: 1"
    And I should see "Country: Austria"
    And I should see "Projects of Gregor"
    And I should see "project 3"
    But I should not see "Christian"
    And I should not see "project 1"
    And I should not see "project 2"

  Scenario: at a profile page there should always all projects be visible
    Given I am on "/app/user/3"
    Then I should see "project 4"
    And I should see "project 5"
    And I should see "project 6"
    And I should see "project 7"
    And I should see "project 8"
    And I should see "project 9"
    And I should see "project 10"
    And I should see "project 11"
    And I should see "project 12"
    And I should see "project 13"
    And I should see "project 14"
    And I should see "project 15"
    And I should see "project 16"
    And I should see "project 17"
    And I should see "project 18"
    And I should see "project 19"
    And I should see "project 20"
    And I should see "project 21"
    And I should see "project 22"
    And I should see "project 23"
    And I should see "project 24"
    And I should see "project 25"
    And I should see "project 26"
    And I should see "project 27"
    And I should see "project 28"
