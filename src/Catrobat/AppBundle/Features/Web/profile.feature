@homepage
Feature: As a visitor I want to see user profiles

  Background:
    Given there are users:
      | name      | password | token      |
      | Christian | 123456   | cccccccccc |
      | Gregor    | 654321   | cccccccccc |
    And there are programs:
      | id | name      | description        | owned by  | downloads | views | upload time      | version |
      | 1  | program 1 | p1                 | Christian | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 | abcef              | Christian | 333       | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 | mein Super Program | Gregor    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

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