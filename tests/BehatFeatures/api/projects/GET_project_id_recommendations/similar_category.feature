@api @projects
Feature: Get recommended project based on a specific project

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are programs:
      | id | name      |
      | 1  | project 1 |

  Scenario: Invalid Request Header
    When I request "GET" "/api/project/1/recommendations?category=similar"
    Then the response status code should be "406"

  Scenario: Not found
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/0/recommendations?category=similar"
    Then the response status code should be "404"

  Scenario: Get similar projects
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/1/recommendations?category=similar"
    Then the response status code should be "200"