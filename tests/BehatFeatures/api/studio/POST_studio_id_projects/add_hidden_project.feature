@api @studio
Feature: Members can only add their own projects or publicly visible ones to a studio

  Background:
    Given there are users:
      | id | name   |
      | 1  | Admin  |
      | 2  | Member |
      | 3  | Owner  |
    And there are projects:
      | id | name           | owned by | visible | auto_hidden | private |
      | 1  | OthersPublic   | Owner    | true    | false       | false   |
      | 2  | OthersHidden   | Owner    | false   | false       | false   |
      | 3  | OthersAutoHid  | Owner    | true    | true        | false   |
      | 4  | OthersPrivate  | Owner    | true    | false       | true    |
      | 5  | MyPrivate      | Member   | true    | false       | true    |
    And there are studios:
      | id | name    | description | is_public |
      | 1  | Studio1 | Test studio | true      |
    And there are studio users:
      | id | user   | studio_name | role   |
      | 1  | Admin  | Studio1     | admin  |
      | 2  | Member | Studio1     | member |

  Scenario Outline: Someone else's hidden or private project cannot be added
    Given I use a valid JWT Bearer token for "<user>"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "<project_id>"}
      """
    When I POST "/api/studios/1/projects"
    Then the response status code should be "404"

    Examples:
      | user   | project_id |
      | Member | 2          |
      | Member | 3          |
      | Member | 4          |
      | Admin  | 4          |

  Scenario Outline: Publicly visible and own projects can be added
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_id": "<project_id>"}
      """
    When I POST "/api/studios/1/projects"
    Then the response status code should be "201"

    Examples:
      | project_id |
      | 1          |
      | 5          |

  Scenario: Batch add reports someone else's private project as not found
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"project_ids": ["1", "4"]}
      """
    When I POST "/api/studios/1/batch-add-projects"
    Then the response status code should be "200"
    And the client response should contain "not_found"
