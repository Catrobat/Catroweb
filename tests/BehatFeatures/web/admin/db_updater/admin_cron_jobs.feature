@admin
Feature: The admin cron jobs view provides a detailed list about all cron jobs and allows to reset and trigger them

  Background:
    Given there are admins:
      | name  |
      | Admin |

  Scenario: All cron jobs should be shown with some statistics
    Given I run the cron job command
    And I log in as "Admin"
    When I am on "/admin/system/cron-job/list"
    And I wait for the page to be loaded
    Then I should see "Cron Jobs"
    And I should see the cron jobs table:
      | Name                                                         | State | Cron Interval | Start At | Duration | Result |
      | (Re-)Add project extensions                                  | idle  | 1 year        |          |          | OK     |
      | Add bronze_user UserAchievements                             | idle  | 1 year        |          |          | OK     |
      | Add diamond_user UserAchievements                            | idle  | 1 week        |          |          | OK     |
      | Add gold_user UserAchievements                               | idle  | 1 week        |          |          | OK     |
      | Add perfect_profile UserAchievements                         | idle  | 1 year        |          |          | OK     |
      | Add silver_user UserAchievements                             | idle  | 1 week        |          |          | OK     |
      | Add verified_developer (gold) UserAchievements               | idle  | 1 week        |          |          | OK     |
      | Add verified_developer (silver) UserAchievements             | idle  | 1 week        |          |          | OK     |
      | Archive log files                                            | idle  | 1 day         |          |          | OK     |
      | Clean compressed project files                               | idle  | 1 week        |          |          | OK     |
      | Clean old extracted project files                            | idle  | 1 day         |          |          | OK     |
      | Clean old log files                                          | idle  | 1 week        |          |          | OK     |
      | Clean unverified user accounts                               | idle  | 1 day         |          |          | OK     |
      | Delete expired projects based on retention rules             | idle  | 1 day         |          |          | OK     |
      | Delete old entries in machine translation table              | idle  | 1 month       |          |          | OK     |
      | Detect broken projects                                       | idle  | 1 day         |          |          | OK     |
      | Garbage collect orphaned assets                              | idle  | 1 week        |          |          | OK     |
      | Re-sanitize user-generated content                           | idle  | 1 month       |          |          | OK     |
      | Remove and add new projects to the random projects' category | idle  | 1 week        |          |          | OK     |
      | Retroactively unlock custom translation achievements         | idle  | 1 month       |          |          | OK     |
      | Update project popularity scores                             | idle  | 6 hours       |          |          | OK     |
      | Update user rankings                                         | idle  | 1 day         |          |          | OK     |

  Scenario: Cron jobs can be manually triggered
    Given I log in as "Admin"
    When I am on "/admin/system/cron-job/list"
    And I wait for the page to be loaded
    Then I should see "Manually trigger the cron job"
    And I should see "Step-by-step guide"
    And the element "#btn-trigger-cron-jobs" should be visible
    And there should be "0" cron jobs in the database
    When I click "#btn-trigger-cron-jobs"
    Then I should see "Cron jobs finished successfully"
    When I am on "/admin/system/cron-job/list"
    And I wait for the page to be loaded
    And there should be "22" cron jobs in the database

  Scenario: Cron jobs can be reset
    Given I log in as "Admin"
    And I run the cron job command
    And I am on "/admin/system/cron-job/list"
    And I wait for the page to be loaded
    Then I should see "Manually trigger the cron job"
    And I should see "Step-by-step guide"
    And the element ".btn-reset-cron-job" should be visible
    When I click ".btn-reset-cron-job"
    And I wait for the page to be loaded
    Then I should see "Resetting cron job successful"
