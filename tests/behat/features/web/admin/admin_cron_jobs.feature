@admin
Feature: The admin cron jobs view provides a detailed list about all cron jobs and allows to reset and trigger them

  Background:
    Given there are admins:
      | name     |
      | Adminius |

  Scenario: All cron jobs should be shown with some stats
    Given I run the cron job command
    And I log in as "Adminius"
    When I am on "/admin/cronjobs/list"
    And I wait for the page to be loaded
    Then I should see "Cron Jobs"
    And I should see the cron jobs table:
      | Name                                            | State | Cron Interval | Start At | End At | Result Code |
      | Add bronze_user UserAchievements                | idle  | 1 year        |          |        | 0           |
      | Add diamond_user UserAchievements               | idle  | 1 week        |          |        | 0           |
      | Add gold_user UserAchievements                  | idle  | 1 week        |          |        | 0           |
      | Add perfect_profile UserAchievements            | idle  | 1 week        |          |        | 0           |
      | Add silver_user UserAchievements                | idle  | 1 week        |          |        | 0           |
      | Add verified_developer UserAchievements         | idle  | 1 year        |          |        | 0           |
      | Delete old entries in machine translation table | idle  | 1 month       |          |        | 0           |

  Scenario: Cron jobs can be manually triggered
    Given I log in as "Adminius"
    When I am on "/admin/cronjobs/list"
    And I wait for the page to be loaded
    Then I should see "Manually trigger the cron job"
    And I should see "Step by step guide"
    And the element "#btn-trigger-cron-jobs" should be visible
    And there should be "0" cron jobs in the database
    When I click "#btn-trigger-cron-jobs"
    Then I should see "Cron jobs finished successfully"
    When I am on "/admin/cronjobs/list"
    And I wait for the page to be loaded
    And there should be "7" cron jobs in the database

  Scenario: Cron jobs can be resetted
    Given I log in as "Adminius"
    And I run the cron job command
    And I am on "/admin/cronjobs/list"
    And I wait for the page to be loaded
    Then I should see "Manually trigger the cron job"
    And I should see "Step by step guide"
    And the element ".btn-reset-cron-job" should be visible
    When I click ".btn-reset-cron-job"
    And I wait for the page to be loaded
    Then I should see "Resetting cron job successful"
