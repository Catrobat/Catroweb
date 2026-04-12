@api @projects @remix-graph
Feature: Get project remix graph

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are projects:
      | id | name      | owned by | visible |
      | 1  | project 1 | Catrobat | true    |
      | 2  | project 2 | User1    | true    |
    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
    And there are backward remix relations:
      | parent_id | child_id |
      | 1         | 2        |
    And there are Scratch remix relations:
      | scratch_parent_id | catrobat_child_id |
      | 12345             | 1                 |

  Scenario: Get remix graph without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/projects/1/remix-graph"
    Then the response status code should be "406"

  Scenario: Get remix graph includes nodes, edges and thumbnail urls
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/remix-graph"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "\"projectId\":\"1\""
    And the client response should contain "\"projectCount\":2"
    And the client response should contain "\"scratchCount\":1"
    And the client response should contain "\"remixCount\":1"
    And the client response should contain "\"source\":\"catrobat\""
    And the client response should contain "images/default/thumbnail.png"
    And the client response should contain "\"source\":\"scratch\""
    And the client response should contain "images/default/not_available.png"
    And the client response should contain "\"from\":\"scratch_12345\""
    And the client response should contain "\"to\":\"catrobat_1\""
