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
      | Name                                                         | State | Cron Interval | Start At | End At | Result Code |
      | (Re-)Add project extensions                                  | idle  | 1 year        |          |        | 0           |
      | Add bronze_user UserAchievements                             | idle  | 1 year        |          |        | 0           |
      | Add diamond_user UserAchievements                            | idle  | 1 week        |          |        | 0           |
      | Add gold_user UserAchievements                               | idle  | 1 week        |          |        | 0           |
      | Add perfect_profile UserAchievements                         | idle  | 1 year        |          |        | 0           |
      | Add silver_user UserAchievements                             | idle  | 1 week        |          |        | 0           |
      | Add verified_developer UserAchievements                      | idle  | 1 week        |          |        | 0           |
      | Archive log files                                            | idle  | 1 day         |          |        | 0           |
      | Clean old extracted project files                            | idle  | 1 day         |          |        | 0           |
      | Clean old log files                                          | idle  | 1 week        |          |        | 0           |
      | Clean unverified user accounts                               | idle  | 1 day         |          |        | 0           |
      | Delete expired projects based on retention rules             | idle  | 1 day         |          |        | 0           |
      | Delete old entries in machine translation table              | idle  | 1 month       |          |        | 0           |
      | Detect broken projects                                       | idle  | 1 day         |          |        | 0           |
      | Garbage collect orphaned assets                              | idle  | 1 week        |          |        | 0           |
      | Remove and add new projects to the random projects' category | idle  | 1 week        |          |        | 0           |
      | Retroactively unlock custom translation achievements         | idle  | 1 month       |          |        | 0           |
      | Update project popularity scores                             | idle  | 6 hours       |          |        | 0           |
      | Update user rankings                                         | idle  | 1 day         |          |        | 0           |

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
    And there should be "19" cron jobs in the database

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
