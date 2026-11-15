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
      | id                                   | reporter | content_type | content_id | category | state    | created_at           |
      | 00000000-0000-0000-0000-000000000401 | Reporter | project      | 1          | spam     | accepted | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/list"
    And I wait for the page to be loaded
    Then the element "a[href*='/admin/moderation/report/00000000-0000-0000-0000-000000000401/acceptReport']" should not exist
    And the element "a[href*='/admin/moderation/report/00000000-0000-0000-0000-000000000401/rejectReport']" should not exist

  Scenario: Resolved appeal rows do not show action buttons
    Given there are moderation appeals:
      | id                                   | appellant | content_type | content_id | reason | state    | created_at           |
      | 00000000-0000-0000-0000-000000000501 | Owner     | project      | 1          | review | approved | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/appeal/list"
    And I wait for the page to be loaded
    Then the element "a[href*='/admin/moderation/appeal/00000000-0000-0000-0000-000000000501/approveAppeal']" should not exist
    And the element "a[href*='/admin/moderation/appeal/00000000-0000-0000-0000-000000000501/rejectAppeal']" should not exist

  Scenario: Direct resolve URL on a resolved report keeps state unchanged
    Given there are moderation reports:
      | id                                   | reporter | content_type | content_id | category | state    | created_at           |
      | 00000000-0000-0000-0000-000000000402 | Reporter | project      | 1          | spam     | accepted | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/00000000-0000-0000-0000-000000000402/acceptReport"
    And I wait for the page to be loaded
    Then I should see "already resolved"
    And moderation report "00000000-0000-0000-0000-000000000402" should have state "accepted"

  Scenario: Direct resolve URL on a resolved appeal keeps state unchanged
    Given there are moderation appeals:
      | id                                   | appellant | content_type | content_id | reason | state    | created_at           |
      | 00000000-0000-0000-0000-000000000502 | Owner     | project      | 1          | review | rejected | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/appeal/00000000-0000-0000-0000-000000000502/approveAppeal"
    And I wait for the page to be loaded
    Then I should see "already resolved"
    And moderation appeal "00000000-0000-0000-0000-000000000502" should have state "rejected"

  Scenario: Pending report rows label the ban action clearly and offer the warn actions
    Given there are moderation reports:
      | id                                   | reporter | content_type | content_id | category | state | created_at          |
      | 00000000-0000-0000-0000-000000000403 | Reporter | project      | 1          | spam     | new   | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/list"
    And I wait for the page to be loaded
    Then I should see "Hide + ban owner"
    And I should see "Hide + warn"
    And the element "form[action*='/admin/moderation/report/00000000-0000-0000-0000-000000000403/hideWarnReport']" should exist

  Scenario: Hide + warn hides the project, warns the owner and does not ban them
    Given there are moderation reports:
      | id                                   | reporter | content_type | content_id | category | state | created_at          |
      | 00000000-0000-0000-0000-000000000404 | Reporter | project      | 1          | spam     | new   | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/list"
    And I wait for the page to be loaded
    When I fill in "message" with "Test warning"
    And I click on the button named "Hide + warn"
    And I wait for the page to be loaded
    Then I should see "owner warned"
    And moderation report "00000000-0000-0000-0000-000000000404" should have state "accepted"
    And the project "1" should be hidden
    And the user "Owner" profile should be visible
    And the user "Owner" should have a moderation warning "Test warning"

  Scenario: Warn keeps the project visible and warns the owner
    Given there are moderation reports:
      | id                                   | reporter | content_type | content_id | category | state | created_at          |
      | 00000000-0000-0000-0000-000000000405 | Reporter | project      | 1          | spam     | new   | 2024-01-01 10:00:00 |
    And I log in as "Admin" with the password "123456"
    And I am on "/admin/moderation/report/list"
    And I wait for the page to be loaded
    When I fill in "message" with "Minor warning"
    And I click on the button named "Warn"
    And I wait for the page to be loaded
    Then I should see "visibility unchanged, owner warned"
    And moderation report "00000000-0000-0000-0000-000000000405" should have state "accepted"
    And the project "1" should be visible
    And the user "Owner" profile should be visible
    And the user "Owner" should have a moderation warning "Minor warning"
