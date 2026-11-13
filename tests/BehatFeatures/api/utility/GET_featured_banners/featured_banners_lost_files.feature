@api
Feature: GET /api/featured-banners skips banners linking to projects whose files were lost

  Background:
    Given there are users:
      | id | name  |
      | 1  | Owner |
    And there are projects:
      | id | name         | owned by | has_missing_files |
      | 1  | IntactProj   | Owner    | false             |
      | 2  | LostProj     | Owner    | true              |
    And there are featured banners:
      | title         | active | priority | type    | project_id |
      | Intact banner | 1      | 2        | project | 1          |
      | Lost banner   | 1      | 1        | project | 2          |

  Scenario: Only the banner of the intact project is returned
    When I request "GET" "/api/featured-banners"
    Then the response status code should be "200"
    And the client response should contain "Intact banner"
    And the client response should not contain "Lost banner"
