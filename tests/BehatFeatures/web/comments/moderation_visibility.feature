@web @project_page
Feature: Comment moderation visibility on detail page

  Background:
    Given there are users:
      | id | name         | admin |
      | 1  | Owner        | false |
      | 2  | OtherUser    | false |
      | 3  | Admin        | true  |
      | 4  | ApprovedUser | false |
    And there are projects:
      | id | name    | owned by |
      | 1  | project | Owner    |
    And there are comments:
      | id | project_id | user_id | text                     | upload_date         | parent_id |
      | 10 | 1          | 1       | hidden owner comment     | 2013-01-01 12:00:00 |           |
      | 20 | 1          | 4       | approved user root       | 2013-01-02 12:00:00 |           |

  Scenario: Hidden comment detail route is blocked for non-owner non-admin
    Given the comment 10 is auto-hidden
    And I log in as "OtherUser"
    And I am on "/app/project/comment/10"
    And I wait for the page to be loaded
    Then the url should match "/app/?$"

  Scenario: Hidden comment detail route is accessible to owner
    Given the comment 10 is auto-hidden
    And I log in as "Owner"
    And I am on "/app/project/comment/10"
    And I wait for the page to be loaded
    Then the url should match "/app/project/comment/10$"
    And I should see "hidden owner comment"

  Scenario: Hidden comment detail route is accessible to admin
    Given the comment 10 is auto-hidden
    And I log in as "Admin"
    And I am on "/app/project/comment/10"
    And I wait for the page to be loaded
    Then the url should match "/app/project/comment/10$"
    And I should see "hidden owner comment"

  Scenario: Report button is hidden on detail page for comments by approved users
    Given the users are approved:
      | name         |
      | ApprovedUser |
    And I log in as "OtherUser"
    And I am on "/app/project/comment/20"
    And I wait for the page to be loaded
    Then the element "#comment-report-button-20" should not exist
