@api @studio
Feature: A studio's projects_count counts only the projects its project list shows the viewer

  Background:
    Given there are users:
      | id | name      | is_minor | consent_status | parent_email   |
      | 1  | Admin     | false    | not_required   |                |
      | 2  | Owner     | false    | not_required   |                |
      | 3  | Stranger  | false    | not_required   |                |
      | 4  | MinorUser | true     | granted        | parent@test.at |
    And there are projects:
      | id | name           | owned by | visible | auto_hidden | private | not_for_kids |
      | 1  | ShownProject   | Admin    | true    | false       | false   | 0            |
      | 2  | HiddenProject  | Owner    | false   | false       | false   | 0            |
      | 3  | AutoHiddenProj | Owner    | true    | true        | false   | 0            |
      | 4  | PrivateProject | Owner    | true    | false       | true    | 0            |
      | 5  | AdultProject   | Admin    | true    | false       | false   | 1            |
    And there are studios:
      | id | name    | description | is_public |
      | 1  | Studio1 | Test studio | true      |
    And there are studio users:
      | id | user  | studio_name | role   |
      | 1  | Admin | Studio1     | admin  |
      | 2  | Owner | Studio1     | admin  |
    And there are studio projects:
      | user  | studio_name | project_name   |
      | Admin | Studio1     | ShownProject   |
      | Owner | Studio1     | HiddenProject  |
      | Owner | Studio1     | AutoHiddenProj |
      | Owner | Studio1     | PrivateProject |
      | Admin | Studio1     | AdultProject   |

  Scenario: Guests count only publicly visible projects
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "projects_count" should be "2"

  Scenario: Other users count only publicly visible projects
    Given I use a valid JWT Bearer token for "Stranger"
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "projects_count" should be "2"

  Scenario: The owner also counts their own hidden and private projects
    Given I use a valid JWT Bearer token for "Owner"
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "projects_count" should be "5"

  Scenario: Minors do not count not-for-kids projects
    Given I use a valid JWT Bearer token for "MinorUser"
    When I GET "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "projects_count" should be "1"

  Scenario: The studio list counts the same way
    When I GET "/api/studios"
    Then the response status code should be "200"
    And the JSON response field "data.0.projects_count" should be "2"

  Scenario: The studio list counts the owner's own projects for the owner
    Given I use a valid JWT Bearer token for "Owner"
    When I GET "/api/studios"
    Then the response status code should be "200"
    And the JSON response field "data.0.projects_count" should be "5"

  Scenario: A user's studio list counts the same way
    When I GET "/api/users/2/studios"
    Then the response status code should be "200"
    And the JSON response field "data.0.projects_count" should be "2"

  Scenario: Studio search counts only publicly visible projects for guests
    Given I wait for the search index to be updated
    When I GET "/api/search?query=Studio1&type=studios"
    Then the response status code should be "200"
    And the JSON response field "studios.0.projects_count" should be "2"

  Scenario Outline: Studio search counts the viewer's visible projects
    Given I use a valid JWT Bearer token for "<viewer>"
    And I wait for the search index to be updated
    When I GET "/api/search?query=Studio1&type=studios"
    Then the response status code should be "200"
    And the JSON response field "studios.0.projects_count" should be "<count>"

    Examples:
      | viewer    | count |
      | Owner     | 5     |
      | Stranger  | 2     |
      | MinorUser | 1     |

  Scenario Outline: Updating a studio returns the viewer's visible project count
    Given I use a valid JWT Bearer token for "<viewer>"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have the POST parameters:
      | name        | value               |
      | description | Updated description |
    When I request "PATCH" "/api/studios/1"
    Then the response status code should be "200"
    And the JSON response field "projects_count" should be "<count>"

    Examples:
      | viewer | count |
      | Owner  | 5     |
      | Admin  | 2     |

  Scenario: Creating a studio returns an empty project count
    Given I use a valid JWT Bearer token for "Owner"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have the POST parameters:
      | name        | value         |
      | name        | New Studio    |
      | description | A new studio  |
    When I request "POST" "/api/studios"
    Then the response status code should be "201"
    And the JSON response field "projects_count" should be "0"
