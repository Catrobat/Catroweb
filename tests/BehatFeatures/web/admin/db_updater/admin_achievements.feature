@admin
Feature: The admin achievements view provides a detailed list about all achievements and allows to update them.

  Background:
    Given there are admins:
      | name  |
      | Admin |
    Given there are achievements:
      | id | internal_title  | internal_description | priority | banner_color | enabled |
      | 1  | best_user_badge | best__desc           | 777      | #ff0000      | 1       |
      | 2  | achiever_badge  | first__desc          | 1234     | #00ff00      | 1       |
    And there are users:
      | id | name     |
      | 1  | Achiever |
      | 2  | Catrobat |
    And there are user achievements:
      | id | user     | achievement     | seen_at | unlocked_at |
      | 2  | Achiever | best_user_badge |         | 2021-05-05  |
      | 2  | Achiever | achiever_badge  |         | 2021-05-03  |
      | 3  | Catrobat | achiever_badge  |         | 2021-05-03  |

  Scenario: All achievements should be displayed with some stats
    Given I log in as "Admin"
    And I am on "/admin/achievements/list"
    And I wait for the page to be loaded
    Then I should see "Achievements"
    And I should see the achievements table:
      | Priority | Internal Title  | Internal Description | Badge | Badge Locked | Color   | Enabled | Unlocked by |
      | 777      | best_user_badge | best__desc           |       |              | #ff0000 | yes     | 1 users     |
      | 1234     | achiever_badge  | first__desc          |       |              | #00ff00 | yes     | 2 users     |

  Scenario: Achievements can be updated
    Given I log in as "Admin"
    And I am on "/admin/achievements/list"
    And I wait for the page to be loaded
    Then I should see "Achievements"
    And I should see "Step-by-step guide"
    And the element "#btn-update-achievements" should be visible
    And there should be "2" achievements in the database
    When I click "#btn-update-achievements"
    And I wait for the page to be loaded
    Then I should see "Achievements have been successfully updated"
    Then there should be "12" achievements in the database
