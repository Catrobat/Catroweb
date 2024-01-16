@web @notifications
Feature: User gets notifications for new followers, reactions, comments, ..


  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |

    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
      | 2  | program 2 | User     |

  Scenario: Notification page must have multiple tabs
    Given I log in as "Catrobat"
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    And I click "#sidebar-notifications"
    And I wait for the page to be loaded
    Then I should be on "/app/user_notifications"
    And I should see "It looks like you don't have any notifications."
    And the element "#all-notif" should be visible
    And the element "#follow-notif" should be visible
    And the element "#reaction-notif" should be visible
    And the element "#comment-notif" should be visible
    And the element "#remix-notif" should be visible

  Scenario: Every empty notification tab shows a message
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    When I click "#follow-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any follower notifications."
    When I click "#reaction-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any reaction notifications."
    When I click "#comment-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any comment notifications."
    When I click "#remix-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any remix notifications."

  Scenario: Notifications tabs contain only notifications of their type
    Given there are "1" "like" notifications for project "program 1" from "User"
    And there are "1" "comment" notifications for project "program 1" from "User"
    And there are "1" "remix" notifications for project "program 1" from "User"
    And there is a notification that "User" follows "Catrobat"

    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User is now following you."
    And I should see "User created a remix program 1 of your game program 1."
    And I should see "User commented on program 1."
    And I should see "User reacted to program 1."

    When I click "#reaction-notif"
    Then I should not see "User is now following you."
    And I should not see "User created a remix program 1 of your game program 1."
    And I should not see "User commented on program 1."
    But I should see "User reacted to program 1."

    When I click "#follow-notif"
    And I wait for the page to be loaded
    Then I should see "User is now following you."
    But I should not see "User created a remix program 1 of your game program 1."
    And I should not see "User commented on program 1."
    And I should not see "User reacted to program 1."

    When I click "#comment-notif"
    And I wait for the page to be loaded
    Then I should not see "User is now following you."
    And I should not see "User created a remix program 1 of your game program 1."
    And I should not see "User reacted to program 1."
    But I should see "User commented on program 1."

    When I click "#remix-notif"
    And I wait for the page to be loaded
    Then I should see "User created a remix program 1 of your game program 1."
    But I should not see "User is now following you."
    And I should not see "User commented on program 1."
    And I should not see "User reacted to program 1."

    When I click "#all-notif"
    And I wait for the page to be loaded
    Then I should see "User is now following you."
    And I should see "User created a remix program 1 of your game program 1."
    And I should see "User commented on program 1."
    And I should see "User reacted to program 1."

