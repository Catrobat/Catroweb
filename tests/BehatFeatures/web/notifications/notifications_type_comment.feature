@web @notifications
Feature: User gets notifications about comments on their programs

  Background:
    Given there are users:
      | id          | name     |
      | Catrobat-id | Catrobat |
      | User-id     | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
    And there are comments:
      | id | project_id | user_id     | text |
      | 1  | 1          | Catrobat-id | c1   |
      | 2  | 1          | User-id     | c2   |

  Scenario: Users should be notified about comments on their projects in comment category
    Given there are catro notifications:
      | id | user     | type    | commentID |
      | 1  | Catrobat | comment | 2         |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    Then I should see "User commented"
    When I click "#comment-notif"
    And I wait for AJAX to finish
    Then I should see "User commented on program 1"

  Scenario: Users should not be notified about their own comments
    Given there are catro notifications:
      | id | user     | type    | commentID |
      | 1  | Catrobat | comment | 1         |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    Then I should not see "Catrobat commented"

  Scenario:  Clicking on a comment notification should redirect the user to the project page for which the comment was posted
    Given there are catro notifications:
      | id | user     | type    | commentID |
      | 1  | Catrobat | comment | 2         |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    Then I should see "User commented"
    Then I click "#catro-notification-1"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"
