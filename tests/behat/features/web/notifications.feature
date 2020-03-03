@web @notifications
Feature: User gets notifications for new followers, reactions, comments and other types like anniversary

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
      | 3  | Drago    |
      | 4  | John     |
      | 5  | Achiever |
      | 6  | Sue      |
      | 7  | Chris    |
      | 8  | Andrew   |
      | 9  | Peter    |
      | 10 | Karen    |
      | 11 | Brent    |

    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
      | 2  | program 2 | User     |
      | 3  | program 3 | Sue      |

    And there are comments:
      | id  | program_id | user_id | upload_date      | text | user_name | reported |
      | 1   | 2          | 1       | 01.01.2013 12:01 | c1   | Catrobat  | true     |
      | 2   | 1          | 2       | 01.01.2013 12:02 | c2   | User      | true     |

    And there are catro notifications:
      | id  | user     | title                 | message                                               | type            | commentID | like_from | follower_id | program_id  | prize | image_path |
      | 1   | Catrobat |                       |                                                       | comment         | 2         |           |             |             |       |            |
      | 2   | Catrobat |                       |                                                       | like            |           | 2         |             | 3           |       |            |
      | 3   | Catrobat |                       |                                                       | follower        |           |           | 2           |             |       |            |
      | 4   | Catrobat | title                 | Default msg                                           | default         |           |           |             |             |       |            |
      | 5   | Catrobat |                       |                                                       | follow_program  |           |           |             | 2           |       |            |
      | 6   | Catrobat | title                 | Congratulations, you uploaded your first app          | anniversary     |           |           |             |             | prize |            |
      | 7   | Catrobat | title                 | Congratulations, you reached a total of 2 views       | achievement     |           |           |             |             |       | image path |
      | 8   | User     |                       |                                                       | comment         | 1         |           |             |             |       |            |
      | 9   | User     |                       |                                                       | like            |           | 1         |             | 2           |       |            |
      | 10  | User     |                       |                                                       | follower        |           |           | 1           |             |       |            |
      | 11  | User     | title                 | Default msg                                           | default         |           |           |             |             |       |            |
      | 12  | User     |                       |                                                       | follow_program  |           |           |             | 3           |       |            |
      | 13  | User     | title                 | Congratulations, you uploaded your first app          | anniversary     |           |           |             |             | prize |            |
      | 14  | User     | title                 | Congratulations, you uploaded your first app          | achievement     |           |           |             |             |       | image path |
      | 15  | Catrobat | title                 | Broadcast msg                                         | broadcast       |           |           |             |             |       |            |
      | 16  | User     | title                 | Broadcast msg                                         | broadcast       |           |           |             |             |       |            |
      | 17  | Achiever | Achievement - Uploads | Congratulations, you uploaded your first app          | achievement     |           |           |             |             |       |            |
      | 18  | Achiever | Achievement - View    | Congratulations, you reached a total of 2 views       | achievement     |           |           |             |             |       |            |

  Scenario: User views his notifications and sees all of them
    Given I log in as "Achiever"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see "Achievement - Uploads"
    And I should see "Achievement - View"

  Scenario: User views his notifications marks one as seen and does not see it anymore
    Given I log in as "Achiever"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see "Achievement - Uploads"
    And I should see "Achievement - View"
    And I open the menu
    And the ".all-notifications-dropdown" element should contain "2"
    When I click "#mark-as-read-17"
    And I wait for AJAX to finish
    Then the ".all-notifications-dropdown" element should contain "1"
    When I click "#mark-as-read-18"
    And I wait for AJAX to finish
    Then I should see "Old Notifications"
    And I should see "You have no new Notifications"
    But I should not see "#mark-as-read-17"
    And I should not see "#mark-as-read-18"

  Scenario: User should see the amount of his notifications in the menu
    Given I log in as "Achiever"
    And I am on "/app/"
    And I wait for the page to be loaded
    When I open the menu
    And the element "#notifications-dropdown-toggler" should be visible
    And the "#notifications-dropdown-toggler" element should contain "2"

  Scenario: New user should not have any notifications
    Given I log in as "Drago"
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    When I click ".collapsible"
    And I wait for AJAX to finish
    And I click "#btn-notifications"
    And I wait for the page to be loaded
    Then I should be on "/app/notifications/allNotifications"
    And I should see "It looks like you dont have any notifications."
    Given I click ".collapsible"
    And I wait for AJAX to finish
    And I click "#btn-followers"
    And I wait for the page to be loaded
    Then I should be on "/app/notifications/followers"
    Then I should see "Follower Notifications"
    And I should see "It looks like you dont have any notifications."

  Scenario: User marks all notifications as read
    Given there are "20"+ notifications for "John"
    And I log in as "John"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    Then the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "20"
    And I should see "You have 20 new Notifications!"
    And I should not see "Old Notifications"
    And the element "#mark-all-as-seen" should be visible
    Then I click "#mark-all-as-seen"
    And I wait for AJAX to finish
    Then I should see "All notifications have been marked as read."
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "Old Notifications"
    And I should see "You have no new Notifications"
    And the element "#mark-all-as-seen" should not be visible
    And the element ".all-notifications-dropdown" should not be visible
    And the element "#mark-as-read .btn.btn-primary" should not be visible

  Scenario: User deletes all notifications
    Given there are "10"+ notifications for "John"
    And I log in as "John"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see "You have 10 new Notifications!"
    And the element "#mark-all-as-seen" should be visible
    And the element "#delete-all" should be visible
    Then I click "#delete-all"
    And I wait for AJAX to finish
    Then I should see "Are you Sure you want to delete all Notifications?"
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "All notifications have been deleted."
    Then I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should see "It looks like you dont have any notifications."
    And the element "#delete-all" should not be visible
    And the element "#mark-all-as-seen" should not be visible

  Scenario: If user goes to one of the Notifications subsections he should see
  just notifications of that type
    Given there are "10" "like" notifications for program "program 3" from "Andrew"
    And there are "20" "comment" notifications for program "program 3" from "Chris"
    And there are "12" "remix" notifications for program "program 3" from "Chris"
    And there are "5"+ notifications for "Sue"
    And "Catrobat" have just followed "Sue"
    And "Drago" have just followed "Sue"
    And I log in as "Sue"
    And I am on "/app/notifications/likes"
    And I wait for the page to be loaded
    Then I should see "You have 10 new Notifications!"
    And I should see "New reaction"
    Given I am on "/app/notifications/comments"
    And I wait for the page to be loaded
    Then I should see "You have 20 new Notifications!"
    And I should see "New comment"
    Given I am on "/app/notifications/followers"
    And I wait for the page to be loaded
    Then I should see "You have 2 new Notifications"
    And I should see "New follower"
    Given I am on "/app/notifications/remix"
    And I wait for the page to be loaded
    Then I should see "You have 12 new Notifications"
    And I should see "New Remix for Project program 3"
    Given I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see "You have 49 new Notifications"

   Scenario: User should get new program notifications under follower category
     Given I log in as "Peter"
     And I am on "/app/user/10"
     And I wait for the page to be loaded
     And I click "#follow-btn"
     And I wait for the page to be loaded
     And I am on "/app/user/11"
     And I wait for the page to be loaded
     And I click "#follow-btn"
     And I wait for the page to be loaded
     Given I have a project with "url" set to "/app/project/99"
     And User "Karen" uploads the project
     And User "Brent" uploads the project
     And I am on "/app/notifications/followers"
     Then I should see "You have 2 new Notifications"
     And I should see "User Karen created a new Project test"
     And I should see "User Brent created a new Project test"











