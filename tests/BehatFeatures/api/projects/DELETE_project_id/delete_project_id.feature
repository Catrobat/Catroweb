@api @projects
Feature: Delete project

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
      | 4  | project 4 | Catrobat | false   |

  Scenario: Delete project by id
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/1"
    Then the response status code should be "204"
    Then project "project 1" is not visible

  Scenario: Delete project already deleted
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/4"
    Then the response status code should be "404"
    Then project "project 4" is not visible

  Scenario: Delete project with invalid id
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/10"
    Then the response status code should be "404"

  Scenario: trying to delete project of other user
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/2"
    Then the response status code should be "404"

  Scenario: trying to delete project without JWT token
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "DELETE" "/api/project/1"
    Then the response status code should be "401"
