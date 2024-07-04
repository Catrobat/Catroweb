@admin
Feature: Admin Broadcast Notification
  In order to send out notification to all users a broadcast system is needed

  Background:
    Given there are admins:
      | name     | email               |
      | Catrobat | dev1@pocketcode.org |
    And there are users:
      | name      | email               |
      | Catrobat2 | dev2@pocketcode.org |


  Scenario: Send out Notifications
    Given I log in as "Catrobat"
    And I am on "/admin/user-communication/notification/list"
    And I wait for the page to be loaded
    And the element "#msg" should be visible
    And I fill in "msg" with "Test Message"
    And I click on the button named "Send notifications"
    And I wait for AJAX to finish
    Then I should see "OK"
    When I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see text matching "Test Message"
    Then I logout
    When I log in as "Catrobat2"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see text matching "Test Message"

  Scenario: When no notification was sent out, I should not see a notification
    When I log in as "Catrobat2"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should not see text matching "Test Title"
    And I should not see text matching "Test Message"
