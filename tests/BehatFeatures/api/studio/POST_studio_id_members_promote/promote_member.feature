@api @studio
Feature: Promote a studio member to admin

  Background:
    Given there are users:
      | id | name        | password |
      | 1  | StudioAdmin | 123456   |
      | 2  | Member      | 123456   |
      | 3  | NonMember   | 123456   |
    And there are studios:
      | id | name        | description   |
      | 1  | Cool Studio | A cool studio |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Member      | 1         | member |

  Scenario: Admin can promote a member
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I request "POST" "/api/studios/1/members/2/promote"
    Then the response status code should be "204"

  Scenario: Promoted member should now be admin
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I request "POST" "/api/studios/1/members/2/promote"
    Then the response status code should be "204"
    When I request "GET" "/api/studios/1/members"
    Then the response status code should be "200"

  Scenario: Non-admin cannot promote
    Given I use a valid JWT Bearer token for "Member"
    And I request "POST" "/api/studios/1/members/2/promote"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot promote
    Given I request "POST" "/api/studios/1/members/2/promote"
    Then the response status code should be "401"
