@api @projects
Feature: Project retention information in API responses

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are projects:
      | id | name              | owned by | downloads | storage_protected |
      | 1  | Protected Project | Catrobat | 0         | true              |
      | 2  | Popular Project   | Catrobat | 50        |                   |
      | 3  | Normal Project    | Catrobat | 0         |                   |

  Scenario: Retention attributes are returned when requested
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "GET" "/api/projects/user?attributes=id,name,retention_days,retention_expiry"
    Then the response status code should be "200"
    And the client response should contain "retention_days"
    And the client response should contain "retention_expiry"

  Scenario: Storage-protected project has retention_days -1
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "GET" "/api/projects/user?attributes=id,name,retention_days"
    Then the response status code should be "200"
    And the client response should contain "retention_days"

  Scenario: Retention attributes are not returned by default
    Given I use a valid JWT Bearer token for "Catrobat"
    And I request "GET" "/api/projects/user?attributes=id,name"
    Then the response status code should be "200"
