@api @projects
Feature: Get project extensions

  Scenario: Get project extensions without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/projects/extensions"
    Then the response status code should be "406"

  Scenario: Empty array if there are no extensions
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/extensions"
    Then the response status code should be "200"
    Then I should get the json object:
    """
    [
    ]
    """

  Scenario: Get all enabled extensions
    Given there are extensions:
      | id | internal_title | enabled | title_ltm_code |
      | 1  | game           | 1       | __game         |
      | 2  | animation      | 1       | __animation    |
      | 3  | art            | 0       | __art          |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/extensions"
    Then the response status code should be "200"
    Then I should get the json object:
    """
    [
      {
        "id": "game",
        "text": "__game"
      },
      {
        "id": "animation",
        "text": "__animation"
      }
    ]
    """

