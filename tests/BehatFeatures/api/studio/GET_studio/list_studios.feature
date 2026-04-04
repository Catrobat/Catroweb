@api @studio
Feature: List studios

  Background:
    Given there are users:
      | id | name  |
      | 1  | Admin |
      | 2  | User1 |
    And there are studios:
      | id | name    | description     | is_public |
      | 1  | Studio1 | First studio    | true      |
      | 2  | Studio2 | Second studio   | true      |
      | 3  | Studio3 | Private studio  | false     |
    And there are studio users:
      | id | user  | studio_name | role  |
      | 1  | Admin | Studio1     | admin |
      | 2  | Admin | Studio2     | admin |
      | 3  | Admin | Studio3     | admin |

  Scenario: List all public studios
    When I GET "/api/studio"
    Then the response status code should be "200"
    And the client response should contain "Studio1"
    And the client response should contain "Studio2"
    And the client response should contain "has_more"

  Scenario: List studios with limit
    When I GET "/api/studio?limit=1"
    Then the response status code should be "200"
    And the client response should contain "has_more"

  Scenario: Private studios are not included in public list
    When I GET "/api/studio?limit=50"
    Then the response status code should be "200"
    And the client response should contain "Studio1"
    And the client response should contain "Studio2"
    And the client response should not contain "Studio3"

  Scenario: Authenticated user also only sees public studios in list
    Given I use a valid JWT Bearer token for "User1"
    When I GET "/api/studio?limit=50"
    Then the response status code should be "200"
    And the client response should not contain "Studio3"

  Scenario: Invalid cursor returns 400
    When I GET "/api/studio?cursor=invalid!!"
    Then the response status code should be "400"
