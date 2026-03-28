@api @studio
Feature: List studio members

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
    And there are studios:
      | id | name    | description  | is_public |
      | 1  | Studio1 | Test studio  | true      |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |

  Scenario: List members of a public studio
    When I GET "/api/studio/1/members"
    Then the response status code should be "200"
    And the client response should contain "Admin"
    And the client response should contain "Member"
    And the client response should contain "has_more"

  Scenario: List members of non-existent studio returns 404
    When I GET "/api/studio/nonexistent/members"
    Then the response status code should be "404"
