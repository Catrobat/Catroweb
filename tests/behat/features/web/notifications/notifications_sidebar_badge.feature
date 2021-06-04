@web @notifications
Feature: Sidebar should show amount of new notifications in form of a badge

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |

  Scenario: User should see new Notifications and the badge should contain number of new notifications
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "5"

  Scenario: Users without notifications should not see any notifications
    Given I log in as "OtherUser"
    When I open the menu
    Then the element "#sidebar-notifications" should be visible
    But the element "#sidebar-notifications .badge-pill" should not be visible

  Scenario: If user marks the notification as read then the badge should be updated
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "5"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I open the menu
    Then the element ".all-notifications" should not be visible

  Scenario: The amount of notifications in the menu should be capped at 99+
    Given there are "100"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    When I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the element ".all-notifications" should be visible
    And the ".all-notifications" element should contain "99+"
