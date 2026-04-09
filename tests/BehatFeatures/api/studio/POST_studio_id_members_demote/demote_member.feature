@api @studio
Feature: Demote a studio admin to member

  Background:
    Given there are users:
      | id | name    | password |
      | 1  | Admin1  | 123456   |
      | 2  | Admin2  | 123456   |
      | 3  | Member  | 123456   |
      | 4  | NonMember | 123456 |
    And there are studios:
      | id | name        | description   |
      | 1  | Cool Studio | A cool studio |
    And there are studio users:
      | id | user   | studio_id | role   |
      | 1  | Admin1 | 1         | admin  |
      | 2  | Admin2 | 1         | admin  |
      | 3  | Member | 1         | member |

  Scenario: Admin can demote another admin
    Given I use a valid JWT Bearer token for "Admin1"
    And I request "POST" "/api/studio/1/members/2/demote"
    Then the response status code should be "204"

  Scenario: Cannot demote the last remaining admin
    Given I use a valid JWT Bearer token for "Admin1"
    And I request "POST" "/api/studio/1/members/2/demote"
    Then the response status code should be "204"
    When I request "POST" "/api/studio/1/members/1/demote"
    Then the response status code should be "403"

  Scenario: Non-admin cannot demote
    Given I use a valid JWT Bearer token for "Member"
    And I request "POST" "/api/studio/1/members/1/demote"
    Then the response status code should be "403"

  Scenario: Unauthenticated user cannot demote
    Given I request "POST" "/api/studio/1/members/1/demote"
    Then the response status code should be "401"

  Scenario: Cannot demote a non-existent user
    Given I use a valid JWT Bearer token for "Admin1"
    And I request "POST" "/api/studio/1/members/999/demote"
    Then the response status code should be "404"
