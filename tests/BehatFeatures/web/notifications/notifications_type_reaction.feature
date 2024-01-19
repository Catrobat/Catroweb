@web @notifications
Feature: User gets notifications for new followers, reactions, comments, ..


  Background:
    Given there are users:
      | id          | name     |
      | Catrobat-id | Catrobat |
      | User-id     | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
      | 2  | program 2 | User     |

  Scenario: Users should be notified about reactions
    Given there are catro notifications:
      | id | user     | type | like_from | project_id |
      | 1  | Catrobat | like | User-id   | 1          |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "User reacted to"

  Scenario: Users should not be notified about their own reactions
    Given there are catro notifications:
      | id | user     | type | like_from   | project_id |
      | 1  | Catrobat | like | Catrobat-id | 1          |
      | 2  | User     | like | Catrobat-id | 2          |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should not see "Catrobat reacted"

  Scenario: Clicking on a reaction notification should redirect the user to the project page for which the reaction was given
    Given there are catro notifications:
      | id | user     | type | like_from | project_id |
      | 1  | Catrobat | like | User-id   | 1          |
    And I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    When I click "#catro-notification-1"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"





