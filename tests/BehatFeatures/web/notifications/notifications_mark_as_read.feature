@web @notifications
Feature: It should be possible to mark notifications marked as read

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are catro notifications:
      | id | user     | type      | message          |
      | 1  | Catrobat | broadcast | Broadcast msg  1 |

  Scenario: New notifications should be marked
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Broadcast msg 1"
    And the element "#catro-notification-1 .dot" should be visible

  Scenario: After revisiting the notification page, old notifications are marked as read
    Given  I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I click "#catro-notification-1"
    When I reload the page
    And I wait for the page to be loaded
    Then I should see "Broadcast msg 1"
    And the element "#catro-notification-1 .dot" should not exist
