@api @user
Feature: Password changes revoke refresh sessions and rotate only a verified current session

  Background:
    Given there are users:
      | id | name     | password | verified |
      | 1  | Catrobat | Old12345 | true     |
      | 2  | Other    | Old12345 | true     |
    And user "Catrobat" has refresh sessions "current-session,other-session"
    And user "Other" has refresh sessions "foreign-session"
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"

  Scenario: Current cookie proof rotates the current session and rejects every old refresh token
    Given I set authentication cookie "REFRESH_TOKEN" to "current-session"
    And I have the following JSON request body:
      """
      {"current_password":"Old12345","password":"NewPassword123"}
      """
    When I request "PATCH" "/api/users/me"
    Then the response status code should be "204"
    And user "Catrobat" should have "1" refresh sessions
    And user "Other" should have "1" refresh sessions
    And refresh session "current-session" should be revoked
    And refresh session "other-session" should be revoked
    And the authentication cookies should hold a rotated session replacing "current-session"
    Given I have a request header "HTTP_authorization" with value ""
    When I request "GET" "/api/users/me"
    Then the response status code should be "200"

  Scenario Outline: Unproved current sessions preserve no refresh tokens
    Given I set authentication cookie "REFRESH_TOKEN" to "<proof>"
    And I have the following JSON request body:
      """
      {"current_password":"Old12345","password":"NewPassword123"}
      """
    When I request "PATCH" "/api/users/me"
    Then the response status code should be "204"
    And user "Catrobat" should have "0" refresh sessions
    And user "Other" should have "1" refresh sessions
    And the API authentication cookies should be cleared

    Examples:
      | proof           |
      |                 |
      | foreign-session |
      | missing-session |

  Scenario: A dry run and an invalid current password keep refresh sessions
    Given I set authentication cookie "REFRESH_TOKEN" to "current-session"
    And I have the following JSON request body:
      """
      {"dry_run":true,"current_password":"Old12345","password":"NewPassword123"}
      """
    When I request "PATCH" "/api/users/me"
    Then the response status code should be "204"
    And user "Catrobat" should have "2" refresh sessions
    Given I have the following JSON request body:
      """
      {"current_password":"Wrong12345","password":"NewPassword123"}
      """
    When I request "PATCH" "/api/users/me"
    Then the response status code should be "422"
    And user "Catrobat" should have "2" refresh sessions

  Scenario: A username update persists usable current-session proof for a later password change
    Given I have the following JSON request body:
      """
      {"username":"RenamedCatrobat"}
      """
    When I request "PATCH" "/api/users/me"
    Then the response status code should be "204"
    And user "RenamedCatrobat" should have "1" refresh sessions
    And the authentication cookies should hold a rotated session replacing "current-session"
    Given I have a request header "HTTP_authorization" with value ""
    And I have the following JSON request body:
      """
      {"current_password":"Old12345","password":"NewPassword123"}
      """
    When I request "PATCH" "/api/users/me"
    Then the response status code should be "204"
    And user "RenamedCatrobat" should have "1" refresh sessions
    And the authentication cookies should hold a rotated session replacing "current-session"
    When I request "GET" "/api/users/me"
    Then the response status code should be "200"
