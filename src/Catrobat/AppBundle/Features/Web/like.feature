@homepage
Feature: Like feature on program page

  Background:
    Given there are users:
      | name      | password | token      | email               |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org |

    And there are programs:
      | id | name     | description | owned by  | downloads | apk_downloads | views | upload time      | version | remix_root |
      | 1  | Minions  | p1          | Catrobat  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 2  | Minimies | p2          | Catrobat  | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |
      | 3  | otherPro | p3          | OtherUser | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | true       |

  Scenario: Like buttons should appear on program page
    Given I am on "/pocketcode/program/1"
    And the element "#program-like-thumbs-up" should be visible
    And the element "#program-like-smile" should be visible
    And the element "#program-like-love" should be visible
    And the element "#program-like-wow" should be visible

  Scenario: By default both like counters should appear, but the specific like counter should be empty
    Given I am on "/pocketcode/program/1"
    And the element "#program-like-counter" should be visible
    And the element "#program-like-detail-container" should be visible
    And the "#program-like-detail-container" element should contain "&nbsp;"

  Scenario: User should see correct like count
    Given there are likes:
      | username | program_id | type | created at       |
      | Catrobat | 1          | 1    | 01.01.2017 12:00 |

    Given I am on "/pocketcode/program/1"
    And the element "#program-like-counter" should be visible
    And the element "#program-like-detail-container" should be visible
    And the "#program-like-detail-container" element should contain "&nbsp;"
    And the "#program-like-counter" element should contain "1"
    And the "#program-like-detail-container" element should not contain "1"

  Scenario: User is not logged in and should be forwarded to login page if the press on any like button
    Given I am on "/pocketcode/program/1"
    When I click "#program-like-thumbs-up"
    Then I should be on "/pocketcode/login"

  Scenario: I should be able to like a program when I am logged in and it should notify the owner
    Given I log in as "OtherUser" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I click "#program-like-thumbs-up"
    And I wait for a second
    When I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    Then the element "#catro-notification-1" should be visible
    And I should see "OtherUser"
    And the element "#notifications-summary" should be visible
    And I should see "1 new Notification"

  Scenario: I should be able to like multiple programs when I am logged in and it should notify the owner multiple times
    Given I log in as "OtherUser" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I click "#program-like-thumbs-up"
    And I am on "/pocketcode/program/2"
    And I click "#program-like-thumbs-up"
    And I wait for a second
    When I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    Then the element "#catro-notification-1" should be visible
    Then the element "#catro-notification-2" should be visible
    And I should see "OtherUser"
    And the element "#notifications-summary" should be visible
    And I should see "2 new Notifications"

  Scenario: I can't notify myself
    Given I log in as "OtherUser" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I click "#program-like-thumbs-up"
    And I am on "/pocketcode/program/2"
    And I click "#program-like-thumbs-up"
    And I am on "/pocketcode/user/notifications"
    Then the element "#catro-notification-1" should not exist
    And I should see "OtherUser"
    And the element "notifications-summary" should not exist
    And I should not see "new Notification"

  Scenario: I should be able to mark a notifications as read
    Given I log in as "OtherUser" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I click "#program-like-thumbs-up"
    And I am on "/pocketcode/program/2"
    And I click "#program-like-thumbs-up"
    And I wait for a second
    And I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    And the element "#catro-notification-1" should be visible
    And the element "#catro-notification-2" should be visible
    And I should see "OtherUser"
    And the element "#notifications-summary" should be visible
    And I should see "2 new Notifications"
    When I click "#mark-all-as-seen"
    And I wait for fadeEffect to finish
    Then I should see "Done!"
    And the element "#notifications-summary" should not be visible
    And I should not see "new Notification"
    And the element "#catro-notification-1" should not exist
    And the element "#catro-notification-2" should not exist

  Scenario: I should be able to mark all notifications as read at once
    Given I log in as "OtherUser" with the password "123456"
    And I am on "/pocketcode/program/1"
    And I click "#program-like-thumbs-up"
    And I am on "/pocketcode/program/2"
    And I click "#program-like-thumbs-up"
    And I wait for a second
    And I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    And the element "#catro-notification-1" should be visible
    And the element "#catro-notification-2" should be visible
    And I should see "OtherUser"
    And the element "#notifications-summary" should be visible
    And I should see "2 new Notifications"
    When I click "#mark-as-read-2"
    And I wait for fadeEffect to finish
    Then the element "#notifications-summary" should be visible
    And I should see "1 new Notification"
    And the element "#catro-notification-1" should be visible
    And the element "#catro-notification-2" should not exist