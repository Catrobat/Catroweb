@homepage
Feature: User gets notifications for new followers, reactions, comments and other types like anniversary

  Background:
    Given there are users:
      | name      | password | token      | email               | id |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | User      | 123456   | dddddddddd | dev2@pocketcode.org |  2 |
      | Drago     | 123456   | cccccccccc | dev3@pocketcode.org |  3 |
      | John      | 123456   | cccccccccc | dev4@pocketcode.org |  4 |

    And there are programs:
      | id | name      | description             | owned by | downloads | apk_downloads | views | upload time      | version | language version | visible | apk_ready |
      | 1  | program 1 | my superman description | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   | 0.94             | true    | true      |
      | 2  | program 2 | abcef                   | User     | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |
      | 3  | program 3 | abcef                   | Drago   | 333       | 3             | 9     | 22.04.2014 13:00 | 0.8.5   | 0.93             | true    | true      |


  Scenario: User should see notifications dropdown and if he clicks on it he should see subsections
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/"
    And I open the menu
    Then I wait 100 milliseconds
    And the element ".collapsible" should be visible
    And the element ".fa-caret-left" should be visible
    When I click ".collapsible"
    And I wait 50 milliseconds
    Then the element ".fa-caret-down" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible

  Scenario: User should not see all notification subsections on the homepage
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/"
    And I open the menu
    And the element "#notifications-dropdown-content a" should not be visible
    And the element "#notifications-dropdown-content #btn-notifications" should not be visible
    And the element "#notifications-dropdown-content #btn-followers" should not be visible
    And the element "#notifications-dropdown-content #btn-likes" should not be visible
    And the element "#notifications-dropdown-content #btn-comments" should not be visible

  Scenario: User should see all notification subsections on notification pages
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/notifications/allNotifications"
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    Given I am on "/app/notifications/followers"
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    Given I am on "/app/notifications/likes"
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    Given I am on "/app/notifications/comments"
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible


  Scenario: New user should not have any notifications
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/"
    And I open the menu
    Then I wait 300 milliseconds
    When I click ".collapsible"
    And I wait 50 milliseconds
    And I click "#btn-notifications"
    And I wait 50 milliseconds
    Then I should be on "/app/notifications/allNotifications"
    And I should see "It looks like you dont have any notifications."
    Given I click ".collapsible"
    And I wait 50 milliseconds
    And I click "#btn-followers"
    Then I should be on "/app/notifications/followers"
    Then I should see "Follower Notifications"
    And I should see "It looks like you dont have any notifications."


  Scenario: User should see new Notifications and the badge should contain number of new notifications
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/notifications/allNotifications"
    And I open the menu
    Then I wait 300 milliseconds
    Then the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "5"
    And I should see "You have 5 new Notifications!"


  Scenario: If user marks the notification as read then the notification should go the the
            old notifications section and amount text and badge should be updated
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/notifications/allNotifications"
    And I open the menu
    Then the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "5"
    And I should see "You have 5 new Notifications!"
    And I should not see "Old Notifications"
    When I click "#mark-as-read-2"
    And I wait for fadeEffect to finish
    Then I should see "Old Notifications"
    And I should see "You have 4 new Notifications"
    And the element "#mark-as-read-2" should not be visible
    And the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "4"


    Scenario: User marks all notifications as read
      Given there are "20"+ notifications for "Catrobat"
      And I log in as "Catrobat" with the password "123456"
      And I am on "/app/notifications/allNotifications"
      And I open the menu
      Then the element ".all-notifications-dropdown" should be visible
      And the ".all-notifications-dropdown" element should contain "20"
      And I should see "You have 20 new Notifications!"
      And I should not see "Old Notifications"
      And the element "#mark-all-as-seen" should be visible
      Then I click "#mark-all-as-seen"
      And I wait 100 milliseconds
      Then I should see "All notifications have been marked as read."
      When I click ".swal2-confirm"
      And I wait 100 milliseconds
      Then I should see "Old Notifications"
      And I should see "You have no new Notifications"
      And the element "#mark-all-as-seen" should not be visible
      And the element ".all-notifications-dropdown" should not be visible
      And the element "#mark-as-read .btn.btn-primary" should not be visible


  Scenario: User deletes all notifications
      Given there are "10"+ notifications for "Catrobat"
      And I log in as "Catrobat" with the password "123456"
      And I am on "/app/notifications/allNotifications"
      Then I should see "You have 10 new Notifications!"
      And the element "#mark-all-as-seen" should be visible
      And the element "#delete-all" should be visible
      Then I click "#delete-all"
      And I wait 100 milliseconds
      Then I should see "Are you Sure you want to delete all Notifications?"
      And I click ".swal2-confirm"
      And I wait 100 milliseconds
      Then I should see "All notifications have been deleted."
      Then I click ".swal2-confirm"
      And I wait 100 milliseconds
      Then I should see "It looks like you dont have any notifications."
      And the element "#delete-all" should not be visible
      And the element "#mark-all-as-seen" should not be visible


    Scenario: If user goes to one of the Notifications subsections he should see
              just notifications of that type
      Given there are "10" "like"  notifications for program "program 2" from "Catrobat"
      And there are "20" "comment"  notifications for program "program 2" from "Drago"
      And there are "5"+ notifications for "User"
      And "Catrobat" have just followed "User"
      And "Drago" have just followed "User"
      And I log in as "User" with the password "123456"
      And I am on "/app/notifications/likes"
      Then I should see "You have 10 new Notifications!"
      And I should see "New reaction"
      Given I am on "/app/notifications/comments"
      Then I should see "You have 20 new Notifications!"
      And I should see "New comment"
      Given I am on "/app/notifications/followers"
      Then I should see "You have 2 new Notifications"
      And I should see "New follower"
      Given I am on "/app/notifications/allNotifications"
      Then I should see "You have 37 new Notifications"



     Scenario: Each Notification subsection should have badge showing number of new Notifications of
               that type
       Given there are "10" "like"  notifications for program "program 1" from "User"
       And there are "20" "comment"  notifications for program "program 1" from "Drago"
       And there are "5"+ notifications for "Catrobat"
       And "John" have just followed "Catrobat"
       And I log in as "Catrobat" with the password "123456"
       And I open the menu
       When I click ".collapsible"
       And I wait 50 milliseconds
       Then the ".all-notifications" element should contain "36"
       And the ".followers" element should contain "1"
       And the ".likes" element should contain "10"
       And the ".comments" element should contain "20"





      Scenario: New Notification badges should be correctly decremented when one notification has been
                marked as read
        Given there are "10" "like"  notifications for program "program 1" from "User"
        And there are "20" "comment"  notifications for program "program 1" from "Drago"
        And there are "5"+ notifications for "Catrobat"
        And "John" have just followed "Catrobat"
        And I log in as "Catrobat" with the password "123456"
        And I open the menu
        When I click ".collapsible"
        And I wait 50 milliseconds
        Then the ".all-notifications" element should contain "36"
        And the ".followers" element should contain "1"
        And the ".likes" element should contain "10"
        And the ".comments" element should contain "20"
        Given I am on "/app/notifications/likes"
        And I click "#mark-as-read-4"
        And I wait 100 milliseconds
        Then the ".likes" element should contain "9"
        And the ".all-notifications" element should contain "35"
        And the ".comments" element should contain "20"
        Given I am on "/app/notifications/comments/"
        And I click "#mark-all-as-seen"
        And I wait 100 milliseconds
        Then I should see "All notifications have been marked as read."
        When I click ".swal2-confirm"
        And I wait 100 milliseconds
        Then the element ".comments" should not be visible
        And the ".all-notifications" element should contain "15"
        And the ".followers" element should contain "1"
