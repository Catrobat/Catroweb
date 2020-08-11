@web @notifications
Feature: The notification page should have a menu displaying all notification categories

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: User should see the notification subsections on the notification page
    Given I log in as "Catrobat"
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And the element "#all-notif" should be visible
    And the element "#follow-notif" should be visible
    And the element "#reaction-notif" should be visible
    And the element "#comment-notif" should be visible
    And the element "#remix-notif" should be visible


  Scenario: User should not see all notification subsections on the homepage
    Given I log in as "Catrobat"
    And I am on the homepage
    And I wait for the page to be loaded
    When I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the element "#all-notif" should not exist
    And the element "#follow-notif" should not exist
    And the element "#reaction-notif" should not exist
    And the element "#comment-notif" should not exist
    And the element "#remix-notif" should not exist