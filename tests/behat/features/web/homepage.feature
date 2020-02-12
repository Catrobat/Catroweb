@web
Feature: Pocketcode homepage
  In order to access and browse the projects
  As a visitor
  I want to be able to see the homepage

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |
      | 3  | project 3 | User1    |
      | 4  | project 4 | User1    |
      | 5  | project 5 | User1    |
    And following projects are featured:
      | id | project   | url                   | active | priority |
      | 1  | project 1 |                       | no     | 1        |
      | 2  | project 2 |                       | yes    | 3        |
      | 3  | project 3 |                       | yes    | 2        |
      | 4  |           | http://www.google.at/ | yes    | 5        |
      | 5  |           | http://www.orf.at/    | no     | 4        |

  Scenario: Viewing the homepage at website root
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    And I should see newest programs
    And I should see recommended programs
    And I should see most downloaded programs
    And I should see most viewed programs
    And I should see random programs

  Scenario: Welcome Section
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the welcome section
    And I should see the video available at "https://www.youtube.com/embed/BHe2r2WU-T8"
    And I should see "Get it on Google Play"
    And I should see "Get it on IOS"

  Scenario: Cant see the Welcome Section when logged in
    Given I log in as "Catrobat"
    And I go to the homepage
    And I wait for the page to be loaded
    Then I should not see the welcome section

  Scenario: Featured Programs and Urls
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see the featured slider
    And I should see the slider with the values "http://www.google.at/,project 2,project 3"