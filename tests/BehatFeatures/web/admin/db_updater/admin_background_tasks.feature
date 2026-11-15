@admin
Feature: The background tasks page shows the scheduled tasks, the queues and failed jobs, and lets admins rerun tasks

  Background:
    Given there are admins:
      | name  |
      | Admin |

  Scenario: Every scheduled task is listed with its last run
    Given I run the cron job command
    And I log in as "Admin"
    When I am on "/admin/system/background-tasks/list"
    And I wait for the page to be loaded
    Then I should see "Background tasks"
    And I should see the scheduled tasks table:
      | Name                                                         | Interval |
      | (Re-)Add project extensions                                  | 1 year   |
      | Add bronze_user UserAchievements                             | 1 year   |
      | Add diamond_user UserAchievements                            | 1 week   |
      | Add gold_user UserAchievements                               | 1 week   |
      | Add perfect_profile UserAchievements                         | 1 year   |
      | Add silver_user UserAchievements                             | 1 week   |
      | Add verified_developer (gold) UserAchievements               | 1 week   |
      | Add verified_developer (silver) UserAchievements             | 1 week   |
      | Clean compressed project files                               | 1 week   |
      | Clean old extracted project files                            | 1 day    |
      | Clean unverified user accounts                               | 1 day    |
      | Delete expired projects based on retention rules             | 1 day    |
      | Delete old entries in machine translation table              | 1 month  |
      | Detect broken projects                                       | 1 day    |
      | Garbage collect orphaned assets                              | 1 week   |
      | Re-sanitize user-generated content                           | 1 month  |
      | Remove and add new projects to the random projects' category | 1 week   |
      | Retroactively unlock custom translation achievements         | 1 month  |
      | Update project popularity scores                             | 6 hours  |
      | Update user rankings                                         | 1 day    |
      | Clean up background jobs                                     | 1 hour   |
    And there should be "27" cron jobs in the database

  Scenario: The queues are shown, and no job has failed
    Given I log in as "Admin"
    When I am on "/admin/system/background-tasks/list"
    And I wait for the page to be loaded
    Then the element "#queues-table" should be visible
    And I should see "scratch"
    And I should see "uploads"
    And I should see "No job has failed for good."

  Scenario: Tasks that never ran are due at the next tick
    Given I log in as "Admin"
    When I am on "/admin/system/background-tasks/list"
    And I wait for the page to be loaded
    Then I should see "never run"
    And I should see "next tick"
    And there should be "0" cron jobs in the database

  Scenario: A task can be rerun at the next tick
    Given I log in as "Admin"
    And I run the cron job command
    And I am on "/admin/system/background-tasks/list"
    And I wait for the page to be loaded
    Then the element ".btn-reset-cron-job" should be visible
    When I click ".btn-reset-cron-job"
    And I wait for the page to be loaded
    Then I should see "The task runs at the next scheduler tick"
    And there should be "26" cron jobs in the database
