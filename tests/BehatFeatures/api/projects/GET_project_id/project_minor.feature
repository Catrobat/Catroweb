@api @projects
Feature: Minors cannot open other users' not-for-kids projects by ID

  Background:
    Given there are users:
      | id | name       | is_minor | consent_status | parent_email   |
      | 1  | AdultOwner | false    | not_required   |                |
      | 2  | AdultUser  | false    | not_required   |                |
      | 3  | MinorUser  | true     | granted        | parent@test.at |
      | 4  | MinorOwner | true     | granted        | parent@test.at |
    And there are downloadable projects:
      | id | name       | owned by   | not_for_kids |
      | 1  | AdultProj  | AdultOwner | 1            |
      | 2  | SafeProj   | AdultOwner | 0            |
      | 3  | MinorsProj | MinorOwner | 1            |

  Scenario Outline: Minors get 404 for another user's not-for-kids project
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "<url>"
    Then the response status code should be "404"

    Examples:
      | url                                   |
      | /api/projects/1                       |
      | /api/projects/1/catrobat              |
      | /api/projects/1/code-statistics       |
      | /api/projects/1/remix-graph           |
      | /api/projects/1/reactions             |

  Scenario: Minors can still open safe projects
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/2"
    Then the response status code should be "200"
    When I request "GET" "/api/projects/2/catrobat"
    Then the response status code should be "200"

  Scenario: Minor owners can open and download their own not-for-kids project
    Given I use a valid JWT Bearer token for "MinorOwner"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/3"
    Then the response status code should be "200"
    When I request "GET" "/api/projects/3/catrobat"
    Then the response status code should be "200"

  Scenario: Adults can open and download not-for-kids projects
    Given I use a valid JWT Bearer token for "AdultUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/1"
    Then the response status code should be "200"
    When I request "GET" "/api/projects/1/catrobat"
    Then the response status code should be "200"
    When I request "GET" "/api/projects/1/reactions"
    Then the response status code should be "200"

  Scenario: Guests can open and download not-for-kids projects
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/1"
    Then the response status code should be "200"
    When I request "GET" "/api/projects/1/catrobat"
    Then the response status code should be "200"
