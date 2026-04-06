@web @reports
Feature: My Reports page shows user's filed reports

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User2    | 123456   |
    And the users are created at:
      | name     | created_at          |
      | Catrobat | 2024-01-01 12:00:00 |
      | User2    | 2024-01-01 12:00:00 |
    And there are projects:
      | id | name     | owned by | description   |
      | 1  | project1 | Catrobat | mydescription |

  # ---------------------------------------------------------------------------
  # Page access and navigation
  # ---------------------------------------------------------------------------

  Scenario: Guest is redirected to login
    Given I am on "/app/reports"
    And I wait for the page to be loaded
    Then I should be on "/app/login"

  Scenario: Logged-in user can access My Reports page
    Given I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    Then I should see "My Reports"

  Scenario: My Reports page is accessible from sidebar
    Given I log in as "Catrobat"
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    And I click "#sidebar-reports"
    And I wait for the page to be loaded
    Then I should be on "/app/reports"
    And I should see "My Reports"

  # ---------------------------------------------------------------------------
  # Empty state
  # ---------------------------------------------------------------------------

  Scenario: User with no reports sees empty state message
    Given I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "You haven't filed any reports yet."

  # ---------------------------------------------------------------------------
  # Filter chips are visible
  # ---------------------------------------------------------------------------

  Scenario: Reports page has filter chips
    Given I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    Then the element "#all-reports" should be visible
    And the element "#pending-reports" should be visible
    And the element "#accepted-reports" should be visible
    And the element "#rejected-reports" should be visible

  # ---------------------------------------------------------------------------
  # Reports display
  # ---------------------------------------------------------------------------

  Scenario: User sees their filed reports
    Given there are moderation reports:
      | id | reporter | content_type | content_id | category | state | created_at          |
      | 1  | Catrobat | project      | 1          | spam     | new   | 2024-06-01 12:00:00 |
    And I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "Project report"
    And I should see "spam"
    And I should see "Pending"

  Scenario: User sees accepted report with correct badge
    Given there are moderation reports:
      | id | reporter | content_type | content_id | category | state    | created_at          | resolved_at         | resolved_by |
      | 1  | Catrobat | project      | 1          | spam     | accepted | 2024-06-01 12:00:00 | 2024-06-05 12:00:00 | User2       |
    And I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "Accepted"

  Scenario: User sees rejected report with correct badge
    Given there are moderation reports:
      | id | reporter | content_type | content_id | category      | state    | created_at          | resolved_at         | resolved_by |
      | 1  | Catrobat | project      | 1          | inappropriate | rejected | 2024-06-01 12:00:00 | 2024-06-05 12:00:00 | User2       |
    And I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "Rejected"

  # ---------------------------------------------------------------------------
  # Filter tabs
  # ---------------------------------------------------------------------------

  Scenario: Pending filter shows only pending reports
    Given there are moderation reports:
      | id | reporter | content_type | content_id | category | state    | created_at          | resolved_at         | resolved_by |
      | 1  | Catrobat | project      | 1          | spam     | new      | 2024-06-01 12:00:00 |                     |             |
      | 2  | Catrobat | user         | 2          | spam     | accepted | 2024-06-02 12:00:00 | 2024-06-05 12:00:00 | User2       |
    And I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I click "#pending-reports"
    And I wait for AJAX to finish
    And I wait 500 milliseconds
    Then the element "#reports-pending" should be visible
    And I should see "Pending"

  # ---------------------------------------------------------------------------
  # Multiple content types
  # ---------------------------------------------------------------------------

  Scenario: User sees reports for different content types
    Given there are comments:
      | id | project_id | user_id | text    | upload_date         | parent_id |
      | 10 | 1          | 2       | comment | 2024-01-01 12:00:00 |           |
    And there are moderation reports:
      | id | reporter | content_type | content_id | category | state | created_at          |
      | 1  | Catrobat | project      | 1          | spam     | new   | 2024-06-01 12:00:00 |
      | 2  | Catrobat | comment      | 10         | spam     | new   | 2024-06-02 12:00:00 |
    And I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "Project report"
    And I should see "Comment report"

  # ---------------------------------------------------------------------------
  # User cannot see other users' reports
  # ---------------------------------------------------------------------------

  Scenario: User does not see reports filed by other users
    Given there are moderation reports:
      | id | reporter | content_type | content_id | category | state | created_at          |
      | 1  | User2    | project      | 1          | spam     | new   | 2024-06-01 12:00:00 |
    And I log in as "Catrobat"
    And I am on "/app/reports"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "You haven't filed any reports yet."
