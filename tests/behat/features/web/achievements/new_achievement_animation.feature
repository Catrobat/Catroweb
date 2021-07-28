@web @achievements
Feature: Sidebar should show an indication of unseen achievements in form of a badge

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are achievements:
      | id | internal_title     | title_ltm_code | description_ltm_code | priority |
      | 1  | best_user          | best__         | best__desc           | 2        |
      | 2  | first_achiever     | first__        | first__desc          | 3        |
      | 3  | master_of_disaster | ups__          | ups__desc            | 1        |

  Scenario: User should not see an animation if there are no new achievements
    Given there are user achievements:
      | id | user     | achievement        | seen_at    | unlocked_at |
      | 1  | Catrobat | best_user          | 2021-03-03 | 2021-03-03  |
    Given I log in as "Catrobat"
    And I am on "/app/achievements"
    Then I should not see "Congratulations"

  Scenario: If users confirm the new achievement animation the badge should disappear
    Given there are user achievements:
      | id | user     | achievement        | seen_at    | unlocked_at |
      | 1  | Catrobat | master_of_disaster |            | 2021-03-03  |
      | 2  | Catrobat | best_user          | 2021-03-03 | 2021-03-03  |
      | 3  | Catrobat | first_achiever     |            | 2021-03-03  |
    And I log in as "Catrobat"
    When I am on "/app/achievements"
    And I wait for the page to be loaded
    Then I should see "Congratulations"
    When I click ".swal2-confirm"
    And I wait 500 milliseconds
    Then I should not see "Congratulations"