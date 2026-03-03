@web @notifications
Feature: User gets notifications when their content is auto-hidden by community moderation

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |

  Scenario: Owner sees moderation notification when project is auto-hidden
    Given there are catro notifications:
      | id | user     | type       | content_type | content_id | action      | message                                                                                        |
      | 1  | Catrobat | moderation | project      | 1          | auto_hidden | Your project "program 1" has been hidden due to community reports. You may appeal this decision. |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "has been hidden due to community reports"

  Scenario: Moderation notification shows flag icon
    Given there are catro notifications:
      | id | user     | type       | content_type | content_id | action      | message                                                                                        |
      | 1  | Catrobat | moderation | project      | 1          | auto_hidden | Your project "program 1" has been hidden due to community reports. You may appeal this decision. |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then the element ".notification-broadcast-icon" should be visible

  Scenario: Clicking moderation notification redirects to project page
    Given there are catro notifications:
      | id | user     | type       | content_type | content_id | action      | message                                                                                        |
      | 1  | Catrobat | moderation | project      | 1          | auto_hidden | Your project "program 1" has been hidden due to community reports. You may appeal this decision. |
    When I log in as "Catrobat"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I click "#catro-notification-1"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"
