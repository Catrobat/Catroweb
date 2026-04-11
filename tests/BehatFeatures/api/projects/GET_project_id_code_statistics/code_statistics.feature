@api @projects @code-statistics
Feature: Get project code statistics via API

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are projects:
      | id | name       | owned by | visible |
      | 1  | Project1   | Catrobat | true    |
      | 2  | PrivatePrj | Catrobat | false   |

  Scenario: Get code statistics for a project with persisted stats
    Given there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | score_synchronization | score_flow_control | score_user_interactivity | score_data_representation | score_bonus | scoring_version |
      | 1          | 1      | 3       | 10     | 2       | 1     | 0      | 2                 | 3                 | 1                      | 0                     | 2                  | 0                        | 2                         | 2           | rubric_2021_v2  |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/code-statistics"
    Then the response status code should be "200"
    And the client response should contain "score_abstraction"
    And the client response should contain "score_parallelism"
    And the client response should contain "score_synchronization"
    And the client response should contain "score_logical_thinking"
    And the client response should contain "score_flow_control"
    And the client response should contain "score_user_interactivity"
    And the client response should contain "score_data_representation"
    And the client response should contain "score_bonus"
    And the client response should contain "score_total"
    And the client response should contain "scoring_version"
    And the client response should contain "12"
    And the client response should contain "rubric_2021_v2"

  Scenario: Code statistics returns correct score values
    Given there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | score_synchronization | score_flow_control | score_user_interactivity | score_data_representation | score_bonus |
      | 1          | 1      | 5       | 20     | 3       | 2     | 1      | 3                 | 2                 | 1                      | 0                     | 2                  | 1                        | 3                         | 1           |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/code-statistics"
    Then the response status code should be "200"
    And the client response should contain "score_abstraction"
    And the client response should contain "score_data_representation"
    And the client response should contain "score_total"
    And the client response should contain "13"

  Scenario: Code statistics for nonexistent project returns 404
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/nonexistent-id/code-statistics"
    Then the response status code should be "404"

  Scenario: Code statistics for private project returns 404 for anonymous user
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/2/code-statistics"
    Then the response status code should be "404"

  Scenario: Legacy code statistics are refreshed to the current rubric when project files are available
    Given there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | scoring_version |
      | 1          | 1      | 1       | 5      | 1       | 0     | 0      | 0                 | 0                 | 0                      | legacy_keyword_counts_v1 |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/code-statistics"
    Then the response status code should be "200"
    And the client response should contain "score_abstraction"
    And the client response should contain "score_total"
    And the client response should contain "rubric_2021_v2"

  Scenario: Code statistics with all zero scores returns zeros
    Given there are project code statistics:
      | project_id | scenes | scripts | bricks | objects | looks | sounds | score_abstraction | score_parallelism | score_logical_thinking | score_synchronization | score_flow_control | score_user_interactivity | score_data_representation | score_bonus |
      | 1          | 1      | 1       | 5      | 1       | 0     | 0      | 0                 | 0                 | 0                      | 0                     | 0                  | 0                        | 0                         | 0           |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/code-statistics"
    Then the response status code should be "200"
    And the client response should contain "score_abstraction"
    And the client response should contain "score_flow_control"
    And the client response should contain "score_total"
