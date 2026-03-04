Feature: Report button visibility and dialog

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User2    | 123456   |
    And there are projects:
      | id | name     | owned by | description   |
      | 1  | project1 | Catrobat | mydescription |

  # ---------------------------------------------------------------------------
  # Project report button
  # ---------------------------------------------------------------------------

  Scenario: Report button is visible on another users project
    Given I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-project" should exist

  Scenario: Report button is not visible on own project
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-project" should not exist

  Scenario: Report button exists for guest user on project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-project" should exist

  Scenario: Pending project report handoff resumes once after login
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-report-project"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    And I fill in "_username" with "User2"
    And I fill in "_password" with "123456"
    And I press "Login"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then the element ".swal2-popup" should be visible
    And I click ".swal2-cancel"
    And I wait 500 milliseconds
    When I reload the page
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then the element ".swal2-popup" should not exist

  # ---------------------------------------------------------------------------
  # User profile report button
  # ---------------------------------------------------------------------------

  Scenario: Report button is visible on another users profile
    Given I log in as "User2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-user" should exist

  Scenario: Report button is not visible on own profile
    Given I log in as "Catrobat"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-user" should not exist

  # ---------------------------------------------------------------------------
  # Comment report button
  # ---------------------------------------------------------------------------

  Scenario: Report button is visible on another users comment
    Given there are comments:
      | id | project_id | user_id | text           | upload_date         | parent_id |
      | 10 | 1          | 1       | Catrobats text | 2013-01-01 12:00:00 |           |
    And I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-report-button-10" should exist

  Scenario: Report button is not visible on own comment
    Given there are comments:
      | id | project_id | user_id | text       | upload_date         | parent_id |
      | 10 | 1          | 1       | My comment | 2013-01-01 12:00:00 |           |
    And I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-report-button-10" should not exist

  # ---------------------------------------------------------------------------
  # Whitelisted content: report button hidden
  # ---------------------------------------------------------------------------

  Scenario: Report button is not visible on approved project
    And the projects are approved:
      | id |
      | 1  |
    Given I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-project" should not exist

  Scenario: Report button is not visible on project by approved user
    And the users are approved:
      | name     |
      | Catrobat |
    Given I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-project" should not exist

  Scenario: Report button is not visible on approved user profile
    And the users are approved:
      | name     |
      | Catrobat |
    Given I log in as "User2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__btn-report-user" should not exist

  Scenario: Report button is not visible on comment by approved user
    Given there are comments:
      | id | project_id | user_id | text          | upload_date         | parent_id |
      | 10 | 1          | 1       | Approved text | 2013-01-01 12:00:00 |           |
    And the users are approved:
      | name     |
      | Catrobat |
    And I log in as "User2"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-report-button-10" should not exist
