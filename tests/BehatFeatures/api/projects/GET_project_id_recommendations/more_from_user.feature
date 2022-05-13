@api @projects
Feature: Get more projects from an user as recommendation

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are programs:
      | id | name      |
      | 1  | project 1 |

  Scenario: Invalid Request Header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    When I request "GET" "/api/project/1/recommendations?category=more_from_user"
    Then the response status code should be "406"

  Scenario: Not found
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/0/recommendations?category=more_from_user"
    Then the response status code should be "404"

  Scenario: Get more_from_user projects
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/1/recommendations?category=more_from_user"
    Then the response status code should be "200"