@api @search
Feature: Search hides not-for-kids projects from minors, except their own

  Background:
    Given there are users:
      | id | name      | is_minor | consent_status | parent_email   |
      | 1  | Uploader  | false    | not_required   |                |
      | 2  | AdultUser | false    | not_required   |                |
      | 3  | MinorUser | true     | granted        | parent@test.at |
    And there are projects:
      | id | name        | owned by  | not_for_kids |
      | 1  | RocketSafe  | Uploader  | 0            |
      | 2  | RocketAdult | Uploader  | 1            |
      | 3  | RocketOwn   | MinorUser | 1            |
    And I wait for the search index to be updated

  Scenario: Guests find not-for-kids projects
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Rocket&type=projects"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name        |
      | RocketSafe  |
      | RocketAdult |
      | RocketOwn   |

  Scenario: Adults find not-for-kids projects
    Given I use a valid JWT Bearer token for "AdultUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Rocket&type=projects"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name        |
      | RocketSafe  |
      | RocketAdult |
      | RocketOwn   |

  Scenario: Minors do not find other users' not-for-kids projects
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Rocket&type=projects"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name       |
      | RocketSafe |
      | RocketOwn  |
    And the client response should not contain "RocketAdult"

  Scenario: Minors do not find other users' not-for-kids projects in the combined search
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Rocket&type=all"
    Then the response code should be "200"
    And the search response should contain the following projects:
      | Name       |
      | RocketSafe |
      | RocketOwn  |
    And the client response should not contain "RocketAdult"
