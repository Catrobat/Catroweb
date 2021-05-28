@web @achievements
Feature: The sidebar directs users to their achievements

  Background:
    Given there are users:
      | id | name     |
      | 2  | Catrobat |

  Scenario: Users must be logged in to see the achievements overview - logged out
    Given I am on "/app"
    When I open the menu
    Then the element "#sidebar-achievements" should not exist

  Scenario: Users must be logged in to see the achievements overview - logged in
    Given I log in as "Catrobat"
    Given I am on "/app"
    When I open the menu
    Then the element "#sidebar-achievements" should be visible

  Scenario: Achievements sidebar should redirect to the achievements overview
    Given I log in as "Catrobat"
    Given I am on "/app"
    When I open the menu
    And I click "#sidebar-achievements"
    Then I should be on "app/achievements"
