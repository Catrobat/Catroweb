@homepage
Feature: Pocketcode homepage visited programs
  Visited programs should be marked.

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456    | cccccccccc | dev1@pocketcode.org |
    And there are programs:
      | id | name      | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 123           | 9     | 22.04.2014 13:00 | 0.8.5   |

  Scenario: Clicking on a program and then going back to homepage. Now the program should be marked on the homepage.
    Given I am on homepage
    And I should see 1 "#newest #program-1"
    When I click "#newest #program-1"
    And I am on homepage
    Then I should see marked "#newest #program-1"

  Scenario: Visited programs should be marked on the entire page.
    Given I am on "/pocketcode"
    And I should see 1 "#newest #program-1"
    When I click "#newest #program-1"
    And I am on "/pocketcode/profile/1"
    Then I should see marked "#program-1"