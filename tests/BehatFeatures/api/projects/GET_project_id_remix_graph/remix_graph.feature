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
      | 3  | project 3 | Catrobat | true    |
    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |
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

  Scenario: Get remix graph for non-existing project returns 404
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/999/remix-graph"
    Then the response status code should be "404"

  Scenario: Get remix graph includes nodes, edges and thumbnail urls
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/remix-graph"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "projectCount"
    And the client response should contain "scratchCount"
    And the client response should contain "remixCount"
    And the client response should contain "catrobat"
    And the client response should contain "scratch"
    And the client response should contain "images/default/thumbnail.png"
    And the client response should contain "images/default/not_available.png"
    And the client response should contain "scratch_12345"
    And the client response should contain "catrobat_1"

  Scenario: Get remix graph for project with no remix relations returns single node
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/3/remix-graph"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "catrobat_3"
    And the client response should contain "project 3"
    And the client response should contain "Catrobat"

  Scenario: Get remix graph returns correct node structure
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/2/remix-graph"
    Then the response status code should be "200"
    And the response should be in json format
    And the client response should contain "project 2"
    And the client response should contain "User1"
    And the client response should contain "thumbnailUrl"

  Scenario: Get remix graph includes cache control header
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/1/remix-graph"
    Then the response status code should be "200"
    And the response Header should contain the key "Cache-Control" with the value 'max-age=300, private'
