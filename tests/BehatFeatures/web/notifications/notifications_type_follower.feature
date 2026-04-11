@web @notifications
Feature: Follower and new programs of users you follow notifications

  Background:
    Given there are users:
      | id          | name     |
      | Catrobat-id | Catrobat |
      | User-id     | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |

  Scenario: Uses should not be notified about follows under follower category
    Given there is a notification that "User" follows "Catrobat"
    When I log in as "Catrobat"
    And I am on "/app/notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "User is now following you"
    Then I click "#follow-notif"
    And I wait for AJAX to finish
    And I should see "User is now following you"

  Scenario: Uses should not be notified about their own follows
    Given there is a notification that "Catrobat" follows "Catrobat"
    When I log in as "Catrobat"
    And I am on "/app/notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I should not see "Catrobat is now following you"

  Scenario: Clicking on a follow notification should redirect the user to his follower page
    Given there is a notification that "User" follows "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "User is now following you"
    And I click ".notification-item"
    And I wait for the page to be loaded
    Then I should be on "/app/follower"


  Scenario: User should get new program notifications from users they are following under follower category
    Given User "Catrobat-id" is followed by user "User-id"
    And there is a project with "url" set to "/app/project/1"
    And user "User" uploads a valid Catrobat project
    When I log in as "Catrobat"
    And I am on "/app/notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "User created a new game"
    When I click "#follow-notif"
    And I wait for AJAX to finish
    Then I should see "User created a new game"

  Scenario: Clicking on a new program notification should redirect the user to the program page
    Given there are catro notifications:
      | id                                   | user | type           | follower_id | project_id |
      | 00000000-0000-0000-0000-000000000001 | User | follow_project |             | 1          |
    And I log in as "User"
    And I am on "/app/notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#catro-notification-00000000-0000-0000-0000-000000000001"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"
