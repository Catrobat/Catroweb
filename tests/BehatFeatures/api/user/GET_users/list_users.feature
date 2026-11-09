@api @user
Feature: List users

  GET /api/users without a query lists accounts newest first. Accounts that are suspended
  (profile hidden), disabled, or without parental consent (pending or revoked) are left out, as on their profiles.

  Background:
    Given there are users:
      | id | name     | enabled | profile_hidden | consent_status |
      | 1  | Viewer   | true    | false          | not_required   |
      | 2  | Visible  | true    | false          | not_required   |
      | 3  | Suspended| true    | true           | not_required   |
      | 4  | Disabled | false   | false          | not_required   |
      | 5  | Pending  | true    | false          | pending        |
      | 6  | Revoked  | true    | false          | revoked        |

  Scenario: Hidden, disabled and consent-pending accounts are not listed
    Given I use a valid JWT Bearer token for "Viewer"
    When I GET "/api/users"
    Then the response status code should be "200"
    And the client response should contain "Visible"
    And the client response should not contain "Suspended"
    And the client response should not contain "Disabled"
    And the client response should not contain "Pending"
    And the client response should not contain "Revoked"
