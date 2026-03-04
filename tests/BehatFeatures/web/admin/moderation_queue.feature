@admin
Feature: Admin moderation queue guards

  Background:
    Given there are admins:
      | id | name  | password |
      | 1  | Admin | 123456   |
    And there are users:
      | id | name     |
      | 2  | Reporter |
      | 3  | Owner    |
    And there are projects:
      | id | name    | owned by | description |
      | 1  | project | Owner    | test        |

  Scenario: Resolved report rows do not show action buttons
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category | state    | created_at           |
      | 401 | Reporter | project      | 1          | spam     | accepted | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/list"
    And I wait for the page to be loaded
    Then the element "a[href*='/admin/moderation/report/401/acceptReport']" should not exist
    And the element "a[href*='/admin/moderation/report/401/rejectReport']" should not exist

  Scenario: Resolved appeal rows do not show action buttons
    Given there are moderation appeals:
      | id  | appellant | content_type | content_id | reason | state    | created_at           |
      | 501 | Owner     | project      | 1          | review | approved | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/appeal/list"
    And I wait for the page to be loaded
    Then the element "a[href*='/admin/moderation/appeal/501/approveAppeal']" should not exist
    And the element "a[href*='/admin/moderation/appeal/501/rejectAppeal']" should not exist

  Scenario: Direct resolve URL on a resolved report keeps state unchanged
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category | state    | created_at           |
      | 402 | Reporter | project      | 1          | spam     | accepted | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/402/acceptReport"
    And I wait for the page to be loaded
    Then I should see "already resolved"
    And moderation report 402 should have state "accepted"

  Scenario: Direct resolve URL on a resolved appeal keeps state unchanged
    Given there are moderation appeals:
      | id  | appellant | content_type | content_id | reason | state    | created_at           |
      | 502 | Owner     | project      | 1          | review | rejected | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/appeal/502/approveAppeal"
    And I wait for the page to be loaded
    Then I should see "already resolved"
    And moderation appeal 502 should have state "rejected"
