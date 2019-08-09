@homepage
Feature: Pocketcode homepage
  In order to access and browse the programs
  As a visitor
  I want to be able to see the homepage

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321   | cccccccccc | dev2@pocketcode.org |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | program 4 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | program 5 |             | User1    | 133       | 63            | 33    | 01.01.2012 13:00 | 0.8.5   |
    And following programs are featured:
      | id | program   | url                   | active | priority |
      | 1  | program 1 |                       | no     | 1        |
      | 2  | program 2 |                       | yes    | 3        |
      | 3  | program 3 |                       | yes    | 2        |
      | 4  |           | http://www.google.at/ | yes    | 5        |
      | 5  |           | http://www.orf.at/    | no     | 4        |

  Scenario: Viewing the homepage at website root
    Given I am on homepage
    Then I should see the featured slider
    And I should see newest programs
    And I should see recommended programs
    And I should see most downloaded programs
    And I should see most viewed programs
    And I should see random programs

  Scenario: Welcome Section
    Given I am on homepage
    Then I should see the welcome section
    And I should see the video available at "https://www.youtube.com/embed/BHe2r2WU-T8"
    And I should see "Get it on Google Play"
    And I should see "Get it on IOS"

  Scenario: Cant see the Welcome Section
    Given I am on homepage
    When I click the "login" button
    Then I should be on "/app/login"
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then I press "Login"
    Then I should not see the welcome section

  Scenario: Login and logout
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click the "login" button
    Then I should be on "/app/login"
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then I press "Login"
    Then I should be logged in

  Scenario: Featured Programs and Urls
    Given I am on homepage
    Then I should see the featured slider
    And I should see the slider with the values "http://www.google.at/,program 2,program 3"