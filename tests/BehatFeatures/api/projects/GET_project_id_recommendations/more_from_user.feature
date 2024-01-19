@api @projects
Feature: Get more projects from an user as recommendation

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |

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

  Scenario: Get more_from_user projects with specified attributes
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "category" with value "more_from_user"
    And I have a parameter "attributes" with value "id,name,author"
    And I request "GET" "/api/project/1/recommendations"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": "2",
        "name": "project 2",
        "author": "Catrobat"
      }
    ]
    """
