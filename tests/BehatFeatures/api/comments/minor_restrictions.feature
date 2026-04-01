Feature: Minor users cannot post comments

  Background:
    Given there are users:
      | id | name      | is_minor | consent_status | parent_email      |
      | 1  | AdultUser | false    | not_required   |                   |
      | 2  | MinorUser | true     | granted        | parent@test.at    |
    And there are projects:
      | id | name     | owned by  |
      | 1  | project1 | AdultUser |

  Scenario: Adult user can post a comment
    Given I use a valid JWT Bearer token for "AdultUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "message": "Hello from adult"
      }
    """
    And I request "POST" "/api/project/1/comments"
    Then the response status code should be "201"

  Scenario: Minor user cannot post a comment
    Given I use a valid JWT Bearer token for "MinorUser"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {
        "message": "Hello from minor"
      }
    """
    And I request "POST" "/api/project/1/comments"
    Then the response status code should be "403"
