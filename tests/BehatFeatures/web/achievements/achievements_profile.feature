@web @achievements
Feature: As a user i want to see other users achievements on a user page

  Background:
    Given there are achievements:
      | id | internal_title     | title_ltm_code | description_ltm_code | priority |
      | 1  | best_user          | best__         | best__desc           | 2        |
      | 2  | first_achiever     | first__        | first__desc          | 3        |
      | 3  | master_of_disaster | ups__          | ups__desc            | 1        |
    And there are users:
      | id | name        |
      | 1  | Achiever    |
      | 2  | NonAchiever |
    And there are user achievements:
      | id | user     | achievement        | seen_at | unlocked_at |
      | 2  | Achiever | best_user          |         | 2021-05-05  |
      | 2  | Achiever | first_achiever     |         | 2021-05-03  |
      | 2  | Achiever | master_of_disaster |         | 2021-05-02  |

  Scenario: If a user has achievements, they should be shown in a horizontal scroller
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    Then the element "#user-achievements" should be visible
    Then the element ".horizontal-scrolling-wrapper" should be visible
    And the "#user-achievements" element should contain "Achievements"
    Then the element ".achievement__badge" should be visible
    And I should see "best__"
    And I should see "first__"
    And I should see "ups__"

  Scenario: User achievements
    Given I am on "/app/user/2"
    And I wait for the page to be loaded
    Then the element "#user-achievements" should not exist