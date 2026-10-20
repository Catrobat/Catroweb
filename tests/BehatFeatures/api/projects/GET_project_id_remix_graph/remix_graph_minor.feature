@api @projects @remix-graph
Feature: Remix graph hides other users' not-for-kids metadata from minors

  Background:
    Given there are users:
      | id | name         | is_minor | consent_status | parent_email   |
      | 1  | MinorOwner   | true     | granted        | parent@test.at |
      | 2  | AdultViewer  | false    | not_required   |                |
      | 3  | MinorViewer  | true     | granted        | parent@test.at |
    And there are projects:
      | id | name           | owned by    | visible | not_for_kids |
      | 1  | RestrictedName | MinorOwner  | true    | 1            |
      | 2  | SafeRemix      | AdultViewer | true    | 0            |
    And there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
    And there are backward remix relations:
      | parent_id | child_id |
      | 1         | 2        |

  Scenario: Minor viewers retain the graph topology without restricted metadata
    Given I use a valid JWT Bearer token for "MinorViewer"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/2/remix-graph"
    Then the response status code should be "200"
    And the response Header should contain the key "Cache-Control" with the value 'no-store, private'
    And the client response should not contain "RestrictedName"
    And the client response should not contain "MinorOwner"
    And the client response should contain "SafeRemix"
    And the client response should contain "AdultViewer"
    And the client response should contain "catrobat_1"
    And the client response should contain "catrobat_2"
    And the JSON response field "nodes.0.available" should be "false"
    And the JSON response field "nodes.0.name" should be "null"
    And the JSON response field "nodes.0.username" should be "null"
    And the JSON response field "projectCount" should be "2"
    And the JSON response field "remixCount" should be "1"
    And the JSON response field "edges.0.from" should be '"catrobat_1"'
    And the JSON response field "edges.0.to" should be '"catrobat_2"'
    And the client response should contain "not_available.png"

  Scenario Outline: Adult viewers and minor owners retain restricted project metadata
    Given I use a valid JWT Bearer token for "<viewer>"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/2/remix-graph"
    Then the response status code should be "200"
    And the response Header should contain the key "Cache-Control" with the value 'no-store, private'
    And the client response should contain "RestrictedName"
    And the client response should contain "MinorOwner"
    And the JSON response field "nodes.0.available" should be "true"

    Examples:
      | viewer      |
      | AdultViewer |
      | MinorOwner  |

  Scenario: Guests retain the existing metadata behavior
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/2/remix-graph"
    Then the response status code should be "200"
    And the response Header should contain the key "Cache-Control" with the value 'no-store, private'
    And the client response should contain "RestrictedName"
    And the client response should contain "MinorOwner"
