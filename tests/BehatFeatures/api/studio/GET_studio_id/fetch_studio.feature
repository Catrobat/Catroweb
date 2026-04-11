@api @studio
Feature: Fetching studio details

  Background:
    Given there are users:
      | id | name       | password |
      | 1  | Non-member | 123456   |
      | 2  | Member     | 123456   |
      | 3  | Admin      | 123456   |
    And there are studios:
      | id | name            | description           | is_public | is_enabled | auto_hidden |
      | 1  | Public studio   | cool description      | true      | true       | false       |
      | 2  | Private Studio  | nothing to see here.. | false     | true       | false       |
      | 3  | Disabled Studio | disabled one          | true      | false      | false       |
      | 4  | Hidden Studio   | auto hidden one       | true      | true       | true        |
    And there are studio users:
      | id | user   | studio_id | role   |
      | 2  | Member | 2         | member |
      | 3  | Admin  | 2         | admin  |
    And there are studio join requests:
      | User       | Studio         | Status  |
      | Non-member | Private Studio | pending |

  Scenario: Non-existing studio returns 404
    And I request "GET" "/api/studio/not-exist"
    Then the response status code should be "404"

  Scenario: Public studios can be seen by everyone with activity and comment counts
    Given I request "GET" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Public studio",
        "description": "cool description",
        "is_public": true,
        "enable_comments": true,
        "image_path": "",
        "members_count": 0,
        "projects_count": 0,
        "activities_count": 0,
        "comments_count": 0
      }
    """

  Scenario: Invite-only studios can be seen by anonymous users
    And I request "GET" "/api/studio/2"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "2",
        "name": "Private Studio",
        "description": "nothing to see here..",
        "is_public": false,
        "enable_comments": true,
        "image_path": "",
        "members_count": 2,
        "projects_count": 0,
        "activities_count": 2,
        "comments_count": 0
      }
    """

  Scenario: Non-member with pending join request sees is_member false and join_request_status
    Given I use a valid JWT Bearer token for "Non-member"
    And I request "GET" "/api/studio/2"
    Then the response status code should be "200"
    And the client response should contain "is_member"
    And the client response should contain "join_request_status"
    And the client response should contain "pending"
    And the client response should not contain "pending_join_requests_count"

  Scenario: Member sees user_role member and is_member true
    Given I use a valid JWT Bearer token for "Member"
    Given I request "GET" "/api/studio/2"
    Then the response status code should be "200"
    And the client response should contain "is_member"
    And the client response should contain "user_role"
    And the client response should contain "member"
    And the client response should not contain "pending_join_requests_count"

  Scenario: Studio admin sees user_role admin and pending join request count
    Given I use a valid JWT Bearer token for "Admin"
    And I request "GET" "/api/studio/2"
    Then the response status code should be "200"
    And the client response should contain "is_member"
    And the client response should contain "user_role"
    And the client response should contain "admin"
    And the client response should contain "pending_join_requests_count"

  Scenario: Disabled studio returns 404
    Given I request "GET" "/api/studio/3"
    Then the response status code should be "404"

  Scenario: Auto-hidden studio returns 404
    Given I request "GET" "/api/studio/4"
    Then the response status code should be "404"

  Scenario: Disabled studio returns 404 even for authenticated users
    Given I use a valid JWT Bearer token for "Member"
    And I request "GET" "/api/studio/3"
    Then the response status code should be "404"

  Scenario: Auto-hidden studio returns 404 even for authenticated users
    Given I use a valid JWT Bearer token for "Member"
    And I request "GET" "/api/studio/4"
    Then the response status code should be "404"
