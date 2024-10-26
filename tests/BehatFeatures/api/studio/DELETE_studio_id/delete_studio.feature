@api @studio
Feature: Updating an existing studio

  Background:
    Given there are users:
      | id | name   | password |
      | 1  | Admin  | 123456   |
      | 2  | Member | 123456   |
    And there are studios:
      | id | name           | description           | is_public |
      | 1  | Public studio  | cool description      | true      |
      | 2  | Private Studio | nothing to see here.. | false     |
    And there are studio users:
      | id | user   | studio_id | role   |
      | 1  | Admin  | 2         | admin  |
      | 2  | Member | 2         | member |

  Scenario: missing jwt token results in an error
    And I request "DELETE" "/api/studio/1"
    Then the response status code should be "401"

  Scenario: Only Studio Admins are allowed to delete studios
    Given I use a valid JWT Bearer token for "Member"
    And I request "DELETE" "/api/studio/2"
    Then the response status code should be "403"

  Scenario: Studios have to exist to delete them
    Given I use a valid JWT Bearer token for "Admin"
    And I request "DELETE" "/api/studio/not-exist"
    Then the response status code should be "404"

  Scenario: public studios can be seen by everyone
    Given I use a valid JWT Bearer token for "Admin"
    When I request "DELETE" "/api/studio/2"
    Then the response status code should be "204"
    Given I use a valid JWT Bearer token for "Admin"
    When I request "DELETE" "/api/studio/2"
    Then the response status code should be "404"
