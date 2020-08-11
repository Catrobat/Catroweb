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
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Congratulations, you uploaded your first app"
    And I should see "Congratulations, you reached a total of 2 views"

  Scenario: User views his notifications marks them as seen by clicking on them
    Given I log in as "Achiever"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "2"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then the element ".all-notifications" should not be visible
    Then I should see "Congratulations, you uploaded your first app"
    And I should see "Congratulations, you reached a total of 2 views"
    And the element "#catro-notification-17 .my-auto.mark-as-read.dot" should not exist
    And the element "#catro-notification-18 .my-auto.mark-as-read.dot" should not exist

  Scenario: New user should not have any notifications
    Given I log in as "Drago"
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
    And I click "#follow-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any follower notifications."
    And I click "#reaction-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any reaction notifications."
    And I click "#comment-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any comment notifications."
    And I click "#remix-notif"
    And I wait for AJAX to finish
    Then I should see "It looks like you don't have any remix notifications."

  Scenario: If user goes to one of the Notifications subsections he should see
  just notifications of that type
    Given there are "10" "like" notifications for program "program 3" from "Andrew"
    And there are "20" "comment" notifications for program "program 3" from "Chris"
    And there are "12" "remix" notifications for program "program 3" from "Chris"
    And there are "5"+ notifications for "Sue"
    And there is a notification that "Catrobat" follows "Sue"
    And there is a notification that "Drago" follows "Sue"
    And I log in as "Sue"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And the element "#all-notif" should be visible
    And the element "#follow-notif" should be visible
    And the element "#reaction-notif" should be visible
    And the element "#comment-notif" should be visible
    And the element "#remix-notif" should be visible
    And I click "#reaction-notif"
    Then I should see "Andrew reacted to program 3."
    And I should not see "Chris commented on program 3"
    And I should not see "Chris created a remix"
    And I should not see "Catrobat is now following you"
    And I should not see "Drago is now following you"
    Then I click "#follow-notif"
    And I wait for AJAX to finish
    Then I should see "Catrobat is now following you"
    And I should see "Drago is now following you"
    And I should not see "Chris commented on program 3"
    And I should not see "Chris created a remix"
    And I should not see "Andrew reacted to program 3."
    Then I click "#comment-notif"
    And I wait for AJAX to finish
    Then I should see "Chris commented on program 3"
    And I should not see "Chris created a remix"
    And I should not see "Andrew reacted to program 3."
    And I should not see "Catrobat is now following you"
    And I should not see "Drago is now following you"
    Then I click "#remix-notif"
    And I wait for AJAX to finish
    Then I should see "Chris created a remix"
    And I should not see "Chris commented on program 3"
    And I should not see "Andrew reacted to program 3."
    And I should not see "Catrobat is now following you"
    And I should not see "Drago is now following you"
    Then I click "#all-notif"
    And I wait for AJAX to finish
    Then I should see "Chris created a remix"
    And I should see "Chris commented on program 3"
    And I should see "Andrew reacted to program 3."
    And I should see "Catrobat is now following you"
    And I should see "Drago is now following you"

  Scenario: User should get new program notifications under follower category
    Given I log in as "Peter"
    And I am on "/app/user/10"
    And I wait for the page to be loaded
    And I click ".profile-follow"
    And I wait for AJAX to finish
    And I am on "/app/user/11"
    And I wait for the page to be loaded
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Given I have a project with "url" set to "/app/project/99"
    And user "Karen" uploads this generated program, API version 1
    And user "Brent" uploads this generated program, API version 1
    And I am on "/app/user_notifications"
    And I should see "Karen created a new game"
    And I should see "Brent created a new game"
    Then I click "#follow-notif"
    And I wait for AJAX to finish
    Then I should see "Karen created a new game"
    And I should see "Brent created a new game"

  Scenario: Clicking on a follow notification should redirect the user to his follower page
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User is now following you"
    And I click "#catro-notification-3"
    And I wait for the page to be loaded
    Then I should be on "/app/follower"

  Scenario: Clicking on a reaction notification should redirect the user to the project page for which the reaction was given
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User reacted to"
    And I click "#catro-notification-2"
    And I wait for the page to be loaded
    Then I should be on "/app/project/3"

  Scenario: Clicking on a comment notification should redirect the user to the project page for which the comment was posted
    Given I log in as "John"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#show-add-comment-button"
    And I wait for AJAX to finish
    And I write "hello" in textbox
    And I wait for AJAX to finish
    When I click "#comment-post-button"
    And I wait for AJAX to finish
    Then I should see "hello"
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for AJAX to finish
    Then the element "#catro-notification-19" should be visible
    And I should see "John commented on program 1"
    Then I click "#catro-notification-19"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"

  Scenario: Clicking on a remix notification should redirect the user to the project page of the remix
    Given I have a project with "url" set to "/app/project/1"
    When user "Drago" uploads this generated program, API version 1
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see "Drago created a remix"
    Then I click "#catro-notification-19"
    And I wait for the page to be loaded
    Then I should see "Drago"
    And I should see "Test"

  Scenario: Clicking on a new program notification should redirect the user to the program page
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User created a new game program 2"
    And I click "#catro-notification-5"
    And I wait for the page to be loaded
    Then I should be on "/app/project/2"




