@api @studio
Feature: Studio project lists hide hidden, private and lost-files projects, except from their owner

  Background:
    Given there are users:
      | id | name     |
      | 1  | Admin    |
      | 2  | Owner    |
      | 3  | Stranger |
    And there are projects:
      | id | name            | owned by | visible | auto_hidden | private | has_missing_files |
      | 1  | ShownProject    | Admin    | true    | false       | false   | false             |
      | 2  | HiddenProject   | Owner    | false   | false       | false   | false             |
      | 3  | AutoHiddenProj  | Owner    | true    | true        | false   | false             |
      | 4  | PrivateProject  | Owner    | true    | false       | true    | false             |
      | 5  | LostFilesProj   | Owner    | true    | false       | false   | true              |
    And there are studios:
      | id | name    | description | is_public |
      | 1  | Studio1 | Test studio | true      |
    And there are studio users:
      | id | user  | studio_name | role   |
      | 1  | Admin | Studio1     | admin  |
      | 2  | Owner | Studio1     | member |
    And there are studio projects:
      | user  | studio_name | project_name   |
      | Admin | Studio1     | ShownProject   |
      | Owner | Studio1     | HiddenProject  |
      | Owner | Studio1     | AutoHiddenProj |
      | Owner | Studio1     | PrivateProject |
      | Owner | Studio1     | LostFilesProj  |

  Scenario: Guests only see publicly visible projects
    When I GET "/api/studios/1/projects"
    Then the response status code should be "200"
    And the client response should contain "ShownProject"
    And the client response should not contain "HiddenProject"
    And the client response should not contain "AutoHiddenProj"
    And the client response should not contain "PrivateProject"
    And the client response should not contain "LostFilesProj"

  Scenario: Other users only see publicly visible projects
    Given I use a valid JWT Bearer token for "Stranger"
    When I GET "/api/studios/1/projects"
    Then the response status code should be "200"
    And the client response should contain "ShownProject"
    And the client response should not contain "HiddenProject"
    And the client response should not contain "AutoHiddenProj"
    And the client response should not contain "PrivateProject"
    And the client response should not contain "LostFilesProj"

  Scenario: The owner still sees their own projects
    Given I use a valid JWT Bearer token for "Owner"
    When I GET "/api/studios/1/projects"
    Then the response status code should be "200"
    And the client response should contain "ShownProject"
    And the client response should contain "HiddenProject"
    And the client response should contain "AutoHiddenProj"
    And the client response should contain "PrivateProject"
    And the client response should contain "LostFilesProj"
