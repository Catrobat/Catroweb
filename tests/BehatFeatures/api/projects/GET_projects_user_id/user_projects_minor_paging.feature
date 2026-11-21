@api @projects
Feature: Minors page through another user's projects without losing pages to hidden ones

  Background:
    Given there are users:
      | id | name      | is_minor | consent_status | parent_email   |
      | 1  | Creator   | false    | not_required   |                |
      | 2  | MinorUser | true     | granted        | parent@test.at |
    And there are projects:
      | id | name      | owned by | not_for_kids | upload time      |
      | 1  | OldSafe   | Creator  | 0            | 01.01.2026 10:00 |
      | 2  | MidAdult  | Creator  | 1            | 02.01.2026 10:00 |
      | 3  | NewSafe   | Creator  | 0            | 03.01.2026 10:00 |

  Scenario: A hidden project inside the first page does not end paging early
    Given I use a valid JWT Bearer token for "MinorUser"
    When I GET "/api/projects/user/1?limit=1"
    Then the response status code should be "200"
    And the client response should contain "NewSafe"
    And the JSON response field "has_more" should be "true"

  Scenario: Minors never get the not-for-kids project
    Given I use a valid JWT Bearer token for "MinorUser"
    When I GET "/api/projects/user/1?limit=5"
    Then the response status code should be "200"
    And the client response should contain "NewSafe"
    And the client response should contain "OldSafe"
    And the client response should not contain "MidAdult"
