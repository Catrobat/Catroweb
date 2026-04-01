@api @user
Feature: Consent-pending and revoked users are blocked from write API actions

  Background:
    Given there are users:
      | id | name         | is_minor | consent_status | parent_email       |
      | 1  | PendingChild | true     | pending        | parent@test.at     |
      | 2  | RevokedChild | true     | revoked        | parent@test.at     |
      | 3  | GrantedChild | true     | granted        | parent@test.at     |
      | 4  | AdultUser    | false    | not_required   |                    |
    And there are projects:
      | id | name     | owned by  |
      | 1  | project1 | AdultUser |

  Scenario: Consent-pending user is blocked from posting comments
    Given I use a valid JWT Bearer token for "PendingChild"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "message": "test"
      }
    """
    And I request "POST" "/api/project/1/comments"
    Then the response status code should be "403"

  Scenario: Consent-revoked user is blocked from posting comments
    Given I use a valid JWT Bearer token for "RevokedChild"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "message": "test"
      }
    """
    And I request "POST" "/api/project/1/comments"
    Then the response status code should be "403"

  Scenario: Consent-granted child can read projects
    Given I use a valid JWT Bearer token for "GrantedChild"
    And I request "GET" "/api/projects?category=recent&limit=1"
    Then the response status code should be "200"

  Scenario: Consent-pending user can still react to projects
    Given I use a valid JWT Bearer token for "PendingChild"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "type": "thumbs_up"
      }
    """
    And I request "POST" "/api/project/1/reaction"
    Then the response status code should be "201"
