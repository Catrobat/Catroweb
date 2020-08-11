@web @notifications
Feature: User should only get notifications from other users, and not from himself


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
      | 3   | 1          | 1       | 01.01.2013 12:03 | c3   | Catrobat  | true     |
      | 4   | 2          | 2       | 01.01.2013 12:04 | c4   | User      | true     |

    And there are catro notifications:
      | id  | user     | title                 | message                                               | type            | commentID | like_from | follower_id | program_id  | prize | image_path |
      | 1   | Catrobat |                       |                                                       | comment         | 2         |           |             |            |       |            |
      | 2   | Catrobat |                       |                                                       | like            |           | 1         |             | 1           |       |            |
      | 3   | Catrobat |                       |                                                       | follower        |           |           | 1           |             |       |            |
      | 4   | Catrobat | title                 | Default msg                                           | default         |           |           |             |             |       |            |
      | 5   | Catrobat |                       |                                                       | follow_program  |           |           |             | 2           |       |            |
      | 6   | Catrobat | title                 | Congratulations, you uploaded your first app          | anniversary     |           |           |             |             | prize |            |
      | 7   | Catrobat | title                 | Congratulations, you reached a total of 2 views       | achievement     |           |           |             |             |       | image path |
      | 8   | User     |                       |                                                       | comment         | 1         |           |             | 2           |       |            |
      | 9   | User     |                       |                                                       | like            |           | 2         |             | 2           |       |            |
      | 10  | User     |                       |                                                       | follower        |           |           | 1           |             |       |            |
      | 11  | User     | title                 | Default msg                                           | default         |           |           |             |             |       |            |
      | 12  | User     |                       |                                                       | follow_program  |           |           |             | 3           |       |            |
      | 13  | User     | title                 | Congratulations, you uploaded your first app          | anniversary     |           |           |             |             | prize |            |
      | 14  | User     | title                 | Congratulations, you uploaded your first app          | achievement     |           |           |             |             |       | image path |
      | 15  | Catrobat | title                 | Broadcast msg                                         | broadcast       |           |           |             |             |       |            |
      | 16  | User     | title                 | Broadcast msg                                         | broadcast       |           |           |             |             |       |            |
      | 17  | Catrobat |                       |                                                       | comment         | 3         |           |             |             |       |            |
      | 18  | User     |                       |                                                       | comment         | 4         |           |             |             |       |            |



  Scenario: Users don't get notifications from themselves
    Given I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Congratulations, you uploaded your first app"
    And I should see "Congratulations, you reached a total of 2 views"
    And I should see "Default msg"
    And I should see "Broadcast msg"
    And I should not see "Catrobat is now following you"
    And I should not see "Catrobat reacted to program 1"
    Then I log in as "User"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Catrobat is now following you"
    And I should see "Default msg"
    And I should see "Broadcast msg"
    And I should not see "User reacted to program 2"

  Scenario: Comment notifications are shown to the user who recieved the comment
    And I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User commented on program 1"
    And I should not see "Catrobat commented on program 1"
    Then I log in as "User"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Catrobat commented on program 2"
    And I should not see "User commented on program 2"
