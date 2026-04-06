@api @studio
Feature: List studio comments

  Background:
    Given there are users:
      | id | name       |
      | 1  | Admin      |
      | 2  | Member     |
      | 3  | Non-member |
    And there are studios:
      | id | name           | description     | is_public |
      | 1  | Studio1        | Test studio     | true      |
      | 2  | PrivateStudio  | Private studio  | false     |
    And there are studio users:
      | id | user   | studio_name   | role   |
      | 1  | Admin  | Studio1       | admin  |
      | 2  | Member | Studio1       | member |
      | 3  | Admin  | PrivateStudio | admin  |
    And there are studio comments:
      | user   | studio_name | comment      |
      | Admin  | Studio1     | Hello world  |
      | Member | Studio1     | Nice studio  |

  Scenario: List comments in a studio
    When I GET "/api/studio/1/comments"
    Then the response status code should be "200"
    And the client response should contain "Hello world"
    And the client response should contain "Nice studio"
    And the client response should contain "has_more"

  Scenario: List comments of non-existent studio returns 404
    When I GET "/api/studio/nonexistent/comments"
    Then the response status code should be "404"

  Scenario: List comments with limit
    When I GET "/api/studio/1/comments?limit=1"
    Then the response status code should be "200"
    And the client response should contain "has_more"

  Scenario: Response contains comment details
    When I GET "/api/studio/1/comments"
    Then the response status code should be "200"
    And the client response should contain "username"
    And the client response should contain "created_at"

  Scenario: Invalid cursor returns 400
    When I GET "/api/studio/1/comments?cursor=invalid!!"
    Then the response status code should be "400"

  Scenario: Non-member cannot list comments of a private studio
    Given I use a valid JWT Bearer token for "Non-member"
    When I GET "/api/studio/2/comments"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot list comments of a private studio
    When I GET "/api/studio/2/comments"
    Then the response status code should be "403"

  Scenario: Member of private studio can list comments
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studio/2/comments"
    Then the response status code should be "200"
