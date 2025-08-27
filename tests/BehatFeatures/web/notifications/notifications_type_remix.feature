@web @notifications @remix
Feature: User gets notifications when somebody uploads a remix of his project

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |

  Scenario: User should get remix notification in a remix category
    Given I have a project with "url" set to "/app/project/1"
    And user "User" uploads this generated project
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User created a remix"
    And the element "#remix-notif" should be visible
    And I click "#remix-notif"
    And I wait for AJAX to finish
    And I should see "User created a remix test of your game project 1."

  Scenario: Uses should not be notified about their own remixes
    Given I have a project with "url" set to "/app/project/1"
    And user "Catrobat" uploads this generated project
    When I log in as "Catrobat"
    And I go to "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "It looks like you don't have any notifications."

  Scenario: Clicking on a remix notification should redirect the user to the project page of the remix
    Given I have a project with "url" set to "/app/project/1"
    And user "User" uploads this generated project, ID '3'
    And I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    When I click "#catro-notification-1"
    And I wait for the page to be loaded
    Then I should be on "/app/project/3"
