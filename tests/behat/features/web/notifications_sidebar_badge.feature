@web @notifications
Feature: Sidebar should show amount of new notifications in form of a badge

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | User      |
      | 3  | Drago     |
      | 4  | John      |
      | 5  | OtherUser |

    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
      | 2  | program 2 | User     |
      | 3  | program 3 | Drago    |

  Scenario: User should see the amount of his notifications in the menu only if he has notifications
    Given I log in as "OtherUser"
    And I am on "/app/"
    And I wait for the page to be loaded
    When I open the menu
    Then the element "#notifications-dropdown-toggler" should be visible
    But the element "#notifications-dropdown-toggler .badge-pill" should not be visible

  Scenario: User should see new Notifications and the badge should contain number of new notifications
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    Then the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "5"
    And I should see "You have 5 new Notifications!"

  Scenario: User should see the amount of his notifications in the menu (capped at 99+)
    Given there are "105"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/"
    And I wait for the page to be loaded
    When I open the menu
    Then the element "#notifications-dropdown-toggler" should be visible
    And the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "99+"

  Scenario: If user marks the notification as read then the notification should go the the
  old notifications section and amount text and badge should be updated
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    Then the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "5"
    And I should see "You have 5 new Notifications!"
    And I should not see "Old Notifications"
    When I click "#mark-as-read-2"
    And I wait for AJAX to finish
    Then I should see "Old Notifications"
    And I should see "You have 4 new Notifications"
    And the element "#mark-as-read-2" should not be visible
    And the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "4"

  Scenario: Each Notification subsection should have badge showing number of new Notifications of that type
    Given there are "10" "like" notifications for program "program 1" from "User"
    And there are "20" "comment" notifications for program "program 1" from "Drago"
    And there are "5"+ notifications for "Catrobat"
    And "John" have just followed "Catrobat"
    And I log in as "Catrobat"
    And I open the menu
    When I click ".collapsible"
    And I wait for AJAX to finish
    Then the ".all-notifications" element should contain "36"
    And the ".followers" element should contain "1"
    And the ".likes" element should contain "10"
    And the ".comments" element should contain "20"

  Scenario: New Notification badges should be correctly decremented when one notification has been marked as read
    Given there are "10" "like" notifications for program "program 1" from "User"
    And there are "20" "comment" notifications for program "program 1" from "Drago"
    And there are "5"+ notifications for "Catrobat"
    And "John" have just followed "Catrobat"
    And I log in as "Catrobat"
    And I open the menu
    When I click ".collapsible"
    And I wait for AJAX to finish
    Then the ".all-notifications" element should contain "36"
    And the ".followers" element should contain "1"
    And the ".likes" element should contain "10"
    And the ".comments" element should contain "20"
    Given I am on "/app/notifications/likes"
    And I click "#mark-as-read-4"
    And I wait for AJAX to finish
    Then the ".likes" element should contain "9"
    And the ".all-notifications" element should contain "35"
    And the ".comments" element should contain "20"
    Given I am on "/app/notifications/comments/"
    And I click "#mark-all-as-seen"
    And I wait for AJAX to finish
    Then I should see "All notifications have been marked as read."
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element ".comments" should not be visible
    And the ".all-notifications" element should contain "15"
    And the ".followers" element should contain "1"