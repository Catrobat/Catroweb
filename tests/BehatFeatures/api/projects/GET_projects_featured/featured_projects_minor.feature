@api @projects
Feature: Featured projects hide not-for-kids projects from minors

  Background:
    Given there are users:
      | id | name      | is_minor | consent_status | parent_email   |
      | 1  | Catrobat  | false    | not_required   |                |
      | 2  | AdultUser | false    | not_required   |                |
      | 3  | MinorUser | true     | granted        | parent@test.at |
    And there are projects:
      | id | name          | owned by | flavor     | not_for_kids |
      | 1  | FeaturedSafe  | Catrobat | pocketcode | 0            |
      | 2  | FeaturedAdult | Catrobat | pocketcode | 1            |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
    And following projects are featured:
      | name          | active | priority | ios_only | flavor     |
      | FeaturedSafe  | 1      | 1        | no       | pocketcode |
      | FeaturedAdult | 1      | 2        | no       | pocketcode |

  Scenario: Adults see featured not-for-kids projects
    Given I use a valid JWT Bearer token for "AdultUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured"
    Then the response status code should be "200"
    And the client response should contain "FeaturedSafe"
    And the client response should contain "FeaturedAdult"

  Scenario: Minors do not see featured not-for-kids projects
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured"
    Then the response status code should be "200"
    And the client response should contain "FeaturedSafe"
    And the client response should not contain "FeaturedAdult"
