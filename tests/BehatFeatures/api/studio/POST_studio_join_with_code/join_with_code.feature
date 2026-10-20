@api @studio
Feature: Join a studio with an invite code

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Pupil1 |
      | 3  | Pupil2 |
      | 4  | Banned |
    And there are studios:
      | id | name     | description | is_public |
      | 1  | Class1   | A class     | false     |
    And there are studios:
      | id | name     | description | is_public | is_enabled |
      | 3  | Gone     | Disabled    | false     | false      |
    And there are studio users:
      | id | user   | studio_name | role   | status |
      | 1  | Admin  | Class1      | admin  | active |
      | 2  | Banned | Class1      | member | banned |
      | 3  | Admin  | Gone        | admin  | active |
    And there are studio invites:
      | studio_name | code       | max_uses | expires_at |
      | Class1      | CLASS1CODE |          |            |
      | Class1      | ONEUSEONLY | 1        |            |
      | Class1      | EXPIREDXYZ |          | 2001-01-01 |
      | Gone        | GONESTUDIO |          |            |

  Scenario: Unauthenticated users cannot join with a code
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "CLASS1CODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "401"

  Scenario: A valid code adds the user as member without approval
    Given I use a valid JWT Bearer token for "Pupil1"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "CLASS1CODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "200"
    And the client response should contain "\"id\":\"1\""
    And the client response should contain "\"is_member\":true"
    When I GET "/api/studios/1/members"
    Then the response status code should be "200"
    And the client response should contain "Pupil1"

  Scenario: Codes ignore case, spaces and dashes
    Given I use a valid JWT Bearer token for "Pupil1"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": " class1-code "}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "200"

  Scenario: Joining twice answers conflict
    Given there are studio users:
      | id | user   | studio_name | role   |
      | 4  | Pupil1 | Class1      | member |
    And I use a valid JWT Bearer token for "Pupil1"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "CLASS1CODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "409"

  Scenario: A banned user cannot rejoin with a code
    Given I use a valid JWT Bearer token for "Banned"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "CLASS1CODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "403"
    When I GET "/api/studios/1/members"
    Then the response status code should be "403"

  Scenario: Unknown, expired and disabled-studio codes are not found
    Given I use a valid JWT Bearer token for "Pupil1"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "NOSUCHCODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "404"
    And I have the following JSON request body:
      """
      {"code": "EXPIREDXYZ"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "404"
    And I have the following JSON request body:
      """
      {"code": "GONESTUDIO"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "404"

  Scenario: A code with max uses stops working once used up
    Given I use a valid JWT Bearer token for "Pupil1"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "ONEUSEONLY"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "200"
    Given I use a valid JWT Bearer token for "Pupil2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "ONEUSEONLY"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "404"

  Scenario: A pending join request is cleared when joining with a code
    Given there are studio join requests:
      | User   | Studio | Status  |
      | Pupil2 | Class1 | pending |
    And I use a valid JWT Bearer token for "Pupil2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"code": "CLASS1CODE"}
      """
    When I POST "/api/studios/join-with-code"
    Then the response status code should be "200"
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/studios/1/join-requests"
    Then the response status code should be "200"
    And the client response should not contain "Pupil2"
