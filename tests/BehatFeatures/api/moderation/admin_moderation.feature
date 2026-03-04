Feature: Admin Moderation API

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And the users are created at:
      | name     | created_at          |
      | Catrobat | 2024-01-01 12:00:00 |
      | User2    | 2024-01-01 12:00:00 |
    And there are admins:
      | name  |
      | Admin |
    And there are projects:
      | id | name     | owned by | description   |
      | 1  | project1 | Catrobat | mydescription |

  # ---------------------------------------------------------------------------
  # GET /api/moderation/reports
  # ---------------------------------------------------------------------------

  Scenario: Admin can list pending reports
    # Create a report first
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam", "note": "Test report"}
      """
    When I request "POST" "/api/project/1/report"
    Then the response status code should be "204"
    # Now list as admin
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/moderation/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "spam"
    And the client response should contain "Test report"

  Scenario: Non-admin cannot list reports (403)
    Given I use a valid JWT Bearer token for "User2"
    When I GET "/api/moderation/reports?limit=20"
    Then the response status code should be "403"

  Scenario: Unauthenticated cannot list reports (401)
    When I GET "/api/moderation/reports?limit=20"
    Then the response status code should be "401"

  Scenario: Admin list reports returns empty when none exist
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/moderation/reports?limit=20"
    Then the response status code should be "200"
    And the client response should contain "has_more"

  # ---------------------------------------------------------------------------
  # PUT /api/moderation/reports/{id}/resolve
  # ---------------------------------------------------------------------------

  Scenario: Admin can accept a report
    # Create a report
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/project/1/report"
    Then the response status code should be "204"
    # Resolve it as admin
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "accept"}
      """
    When I request "PUT" "/api/moderation/reports/1/resolve"
    Then the response status code should be "200"

  Scenario: Admin can reject a report
    # Create a report
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/project/1/report"
    Then the response status code should be "204"
    # Reject it as admin
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "reject"}
      """
    When I request "PUT" "/api/moderation/reports/1/resolve"
    Then the response status code should be "200"

  Scenario: Resolve non-existent report returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "accept"}
      """
    When I request "PUT" "/api/moderation/reports/9999/resolve"
    Then the response status code should be "404"

  Scenario: Resolve report with invalid action returns 400
    # Create a report
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/project/1/report"
    Then the response status code should be "204"
    # Try invalid action
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "invalid_action"}
      """
    When I request "PUT" "/api/moderation/reports/1/resolve"
    Then the response status code should be "400"

  Scenario: Non-admin cannot resolve reports (403)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "accept"}
      """
    When I request "PUT" "/api/moderation/reports/1/resolve"
    Then the response status code should be "403"

  Scenario: Resolving an already resolved report returns 400
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"category": "spam"}
      """
    When I request "POST" "/api/project/1/report"
    Then the response status code should be "204"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "accept"}
      """
    When I request "PUT" "/api/moderation/reports/1/resolve"
    Then the response status code should be "200"
    And I have the following JSON request body:
      """
      {"action": "reject"}
      """
    When I request "PUT" "/api/moderation/reports/1/resolve"
    Then the response status code should be "400"

  Scenario: Report pagination cursor uses created_at and id ordering
    Given there are moderation reports:
      | id  | reporter | content_type | content_id | category | state | created_at           |
      | 101 | User2    | project      | 911        | spam     | new   | 2024-01-01 10:00:00 |
      | 102 | User2    | project      | 912        | spam     | new   | 2024-01-01 10:00:00 |
      | 103 | User2    | project      | 913        | spam     | new   | 2024-01-01 10:00:01 |
    And I use a valid JWT Bearer token for "Admin"
    When I GET "/api/moderation/reports?limit=2"
    Then the response status code should be "200"
    And the client response should contain "next_cursor"
    And the client response should contain "MjAyNC0wMS0wMVQxMDowMDowMCswMDowMHwxMDI="
    When I GET "/api/moderation/reports?limit=2&cursor=MjAyNC0wMS0wMVQxMDowMDowMCswMDowMHwxMDI="
    Then the response status code should be "200"
    And the client response should contain "913"
    And the client response should not contain "911"

  # ---------------------------------------------------------------------------
  # GET /api/moderation/appeals
  # ---------------------------------------------------------------------------

  Scenario: Admin can list pending appeals
    # Create hidden project and appeal
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "My project was hidden unfairly"}
      """
    When I request "POST" "/api/project/1/appeal"
    Then the response status code should be "201"
    # List as admin
    Given I use a valid JWT Bearer token for "Admin"
    When I GET "/api/moderation/appeals?limit=20"
    Then the response status code should be "200"
    And the client response should contain "hidden unfairly"

  Scenario: Non-admin cannot list appeals (403)
    Given I use a valid JWT Bearer token for "User2"
    When I GET "/api/moderation/appeals?limit=20"
    Then the response status code should be "403"

  # ---------------------------------------------------------------------------
  # PUT /api/moderation/appeals/{id}/resolve
  # ---------------------------------------------------------------------------

  Scenario: Admin can approve an appeal
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Wrongly hidden"}
      """
    When I request "POST" "/api/project/1/appeal"
    Then the response status code should be "201"
    # Approve as admin
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "approve", "note": "Content reviewed and restored"}
      """
    When I request "PUT" "/api/moderation/appeals/1/resolve"
    Then the response status code should be "200"

  Scenario: Admin can reject an appeal
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review my project"}
      """
    When I request "POST" "/api/project/1/appeal"
    Then the response status code should be "201"
    # Reject as admin
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "reject", "note": "Content violates guidelines"}
      """
    When I request "PUT" "/api/moderation/appeals/1/resolve"
    Then the response status code should be "200"

  Scenario: Resolve non-existent appeal returns 404
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "approve"}
      """
    When I request "PUT" "/api/moderation/appeals/9999/resolve"
    Then the response status code should be "404"

  Scenario: Resolve appeal with invalid action returns 400
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please review"}
      """
    When I request "POST" "/api/project/1/appeal"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "invalid_action"}
      """
    When I request "PUT" "/api/moderation/appeals/1/resolve"
    Then the response status code should be "400"

  Scenario: Non-admin cannot resolve appeals (403)
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "approve"}
      """
    When I request "PUT" "/api/moderation/appeals/1/resolve"
    Then the response status code should be "403"

  Scenario: Resolving an already resolved appeal returns 400
    Given the project "project1" is auto-hidden
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"reason": "Please restore"}
      """
    When I request "POST" "/api/project/1/appeal"
    Then the response status code should be "201"
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"action": "approve"}
      """
    When I request "PUT" "/api/moderation/appeals/1/resolve"
    Then the response status code should be "200"
    And I have the following JSON request body:
      """
      {"action": "reject"}
      """
    When I request "PUT" "/api/moderation/appeals/1/resolve"
    Then the response status code should be "400"

  Scenario: Appeal pagination cursor uses created_at and id ordering
    Given there are moderation appeals:
      | id  | appellant | content_type | content_id | reason   | state   | created_at           |
      | 201 | Catrobat  | project      | 921        | first    | pending | 2024-01-01 11:00:00 |
      | 202 | Catrobat  | project      | 922        | second   | pending | 2024-01-01 11:00:00 |
      | 203 | Catrobat  | project      | 923        | third    | pending | 2024-01-01 11:00:01 |
    And I use a valid JWT Bearer token for "Admin"
    When I GET "/api/moderation/appeals?limit=2"
    Then the response status code should be "200"
    And the client response should contain "next_cursor"
    And the client response should contain "MjAyNC0wMS0wMVQxMTowMDowMCswMDowMHwyMDI="
    When I GET "/api/moderation/appeals?limit=2&cursor=MjAyNC0wMS0wMVQxMTowMDowMCswMDowMHwyMDI="
    Then the response status code should be "200"
    And the client response should contain "923"
    And the client response should not contain "921"
