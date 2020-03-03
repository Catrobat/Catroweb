@homepage
Feature: Pocketcode homepage visited programs
  Visited programs should be marked.

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |

  Scenario: Clicking on a program and then going back to homepage. Now the program should be marked on the homepage.
    Given I am on the homepage
    And I wait for the page to be loaded
    And I should see 1 "#newest #program-1"
    Then the element "#program-1 visited-program" should not exist
    When I click "#newest #program-1"
    And I wait for the page to be loaded
    When I am on the homepage
    Then I should see marked "#newest #program-1"

  Scenario: Visited programs should be marked on the entire page.
    Given I am on the homepage
    And I wait for the page to be loaded
    And I should see 1 "#newest #program-1"
    When I click "#newest #program-1"
    And I wait for the page to be loaded
    When I am on "/app/user/1"
    And I wait for the page to be loaded
    Then I should see marked "#program-1"
    Then the element "#program-1 visited-program" should not exist