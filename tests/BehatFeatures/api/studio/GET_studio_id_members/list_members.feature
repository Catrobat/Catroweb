@api @studio
Feature: List studio members

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

  Scenario: List members of a public studio
    When I GET "/api/studios/1/members"
    Then the response status code should be "200"
    And the client response should contain "Admin"
    And the client response should contain "Member"
    And the client response should contain "has_more"

  Scenario: List members of non-existent studio returns 404
    When I GET "/api/studios/nonexistent/members"
    Then the response status code should be "404"

  Scenario: List members with limit
    When I GET "/api/studios/1/members?limit=1"
    Then the response status code should be "200"
    And the client response should contain "has_more"

  Scenario: Response contains role information
    When I GET "/api/studios/1/members"
    Then the response status code should be "200"
    And the client response should contain "role"
    And the client response should contain "admin"
    And the client response should contain "member"

  Scenario: Invalid cursor returns 400
    When I GET "/api/studios/1/members?cursor=invalid!!"
    Then the response status code should be "400"

  Scenario: Non-member cannot list members of a private studio
    Given I use a valid JWT Bearer token for "Non-member"
    When I GET "/api/studios/2/members"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot list members of a private studio
    When I GET "/api/studios/2/members"
    Then the response status code should be "403"

  Scenario: Member of private studio can list members
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/2/members"
    Then the response status code should be "200"
