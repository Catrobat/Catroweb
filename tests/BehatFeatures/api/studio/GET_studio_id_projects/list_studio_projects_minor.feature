@api @studio
Feature: Studio project lists hide not-for-kids projects from minors, except their own

  Background:
    Given there are users:
      | id | name      | is_minor | consent_status | parent_email   |
      | 1  | Admin     | false    | not_required   |                |
      | 2  | AdultUser | false    | not_required   |                |
      | 3  | MinorUser | true     | granted        | parent@test.at |
    And there are projects:
      | id | name         | owned by  | not_for_kids |
      | 1  | StudioSafe   | Admin     | 0            |
      | 2  | StudioAdult  | Admin     | 1            |
      | 3  | StudioOwnNfk | MinorUser | 1            |
    And there are studios:
      | id | name    | description | is_public |
      | 1  | Studio1 | Test studio | true      |
    And there are studio users:
      | id | user  | studio_name | role  |
      | 1  | Admin | Studio1     | admin |
    And there are studio projects:
      | user  | studio_name | project_name |
      | Admin | Studio1     | StudioSafe   |
      | Admin | Studio1     | StudioAdult  |
      | Admin | Studio1     | StudioOwnNfk |

  Scenario: Adults see not-for-kids projects in a studio
    Given I use a valid JWT Bearer token for "AdultUser"
    When I GET "/api/studios/1/projects"
    Then the response status code should be "200"
    And the client response should contain "StudioSafe"
    And the client response should contain "StudioAdult"
    And the client response should contain "StudioOwnNfk"

  Scenario: Minors only see their own not-for-kids projects in a studio
    Given I use a valid JWT Bearer token for "MinorUser"
    When I GET "/api/studios/1/projects"
    Then the response status code should be "200"
    And the client response should contain "StudioSafe"
    And the client response should contain "StudioOwnNfk"
    And the client response should not contain "StudioAdult"
