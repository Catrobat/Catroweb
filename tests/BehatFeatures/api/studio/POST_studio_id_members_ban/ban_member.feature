@api @studio
Feature: Ban a studio member

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

  Scenario: Admin can ban a member
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I request "POST" "/api/studio/1/members/2/ban"
    Then the response status code should be "204"

  Scenario: Banned member should no longer appear in members list
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I request "POST" "/api/studio/1/members/2/ban"
    Then the response status code should be "204"
    When I request "GET" "/api/studio/1/members"
    Then the response status code should be "200"

  Scenario: Non-admin cannot ban
    Given I use a valid JWT Bearer token for "Member"
    And I request "POST" "/api/studio/1/members/2/ban"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot ban
    Given I request "POST" "/api/studio/1/members/2/ban"
    Then the response status code should be "401"
