@api @studio
Feature: Updating an existing studio

  Background:
    Given there are users:
      | id | name       | password |
      | 1  | Non-member | 123456   |
      | 2  | Member     | 123456   |
    And there are studios:
      | id | name           | description           | is_public |
      | 1  | Public studio  | cool description      | true      |
      | 2  | Private Studio | nothing to see here.. | false     |
    And there are studio users:
      | id | user       | studio_id | role   |
      | 2  | Member     | 2         | member |

  Scenario: Only Studio Members are allowed to get private studio details
    And I request "GET" "/api/studio/not-exist"
    Then the response status code should be "404"

  Scenario: public studios can be seen by everyone
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
        "image_path": ""
      }
    """

  Scenario: With authentication private studios can not be read
    And I request "GET" "/api/studio/2"
    Then the response status code should be "403"

  Scenario: Only Studio Members are allowed to get private studio details
    Given I use a valid JWT Bearer token for "Non-member"
    And I request "GET" "/api/studio/2"
    Then the response status code should be "403"

  Scenario: Private studios can be seen by members
    Given I use a valid JWT Bearer token for "Member"
    Given I request "GET" "/api/studio/2"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "2",
        "name": "Private Studio",
        "description": "nothing to see here..",
        "is_public": false,
        "enable_comments": true,
        "image_path": ""
      }
    """
