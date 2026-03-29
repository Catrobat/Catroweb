@web @project_page
Feature: As a visitor I want to see inline code statistics on the project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: Project page shows the code statistics toggle button
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#code-statistics-inline" should exist
    And the element "#code-stats-toggle" should be visible

  Scenario: Code statistics panel is hidden by default
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#code-stats-panel" should not be visible

  Scenario: Clicking the toggle button shows the code statistics panel
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | score_synchronization | score_flow_control | score_user_interactivity | score_data_representation |
      | 1          | 1      | 3       | 10     | 2       | 1     | 0      | 2                 | 3                 | 1                      | 0                     | 2                  | 0                        | 2                         |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-stats-toggle"
    And I wait for AJAX to finish
    And I wait 2000 milliseconds
    Then the element "#code-stats-panel" should be visible
    And the element "#code-stats-animation" should exist

  Scenario: Inline code statistics show correct base score total
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | score_synchronization | score_flow_control | score_user_interactivity | score_data_representation |
      | 1          | 1      | 3       | 10     | 2       | 1     | 0      | 2                 | 3                 | 1                      | 0                     | 2                  | 0                        | 2                         |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-stats-toggle"
    And I wait for AJAX to finish
    And I wait 3000 milliseconds
    Then I wait for the element "#code-stats-total-number" to contain "15"

  Scenario: Inline code statistics show zero score when no pre-persisted stats exist
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-stats-toggle"
    And I wait for AJAX to finish
    And I wait 3000 milliseconds
    Then the element "#code-stats-panel" should be visible
    And I wait for the element "#code-stats-total-number" to contain "5"

  Scenario: Clicking toggle again hides the code statistics panel
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking |
      | 1          | 1      | 3       | 10     | 2       | 1     | 0      | 1                 | 1                 | 1                      |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-stats-toggle"
    And I wait for AJAX to finish
    And I wait 1000 milliseconds
    Then the element "#code-stats-panel" should be visible
    When I click "#code-stats-toggle"
    Then the element "#code-stats-panel" should not be visible

  Scenario: Inline code statistics with all zero scores shows zero
    Given there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | score_synchronization | score_flow_control | score_user_interactivity | score_data_representation |
      | 1          | 1      | 1       | 5      | 1       | 0     | 0      | 0                 | 0                 | 0                      | 0                     | 0                  | 0                        | 0                         |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-stats-toggle"
    And I wait for AJAX to finish
    And I wait 3000 milliseconds
    Then I wait for the element "#code-stats-total-number" to contain "5"
