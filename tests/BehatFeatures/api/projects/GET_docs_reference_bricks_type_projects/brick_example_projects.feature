@api @projects
Feature: Example projects that use a brick, for the docs brick pages

  Background:
    Given there are users:
      | id | name     | password | profile_hidden |
      | 1  | Catrobat | 123456   | false          |
      | 2  | Banned   | 123456   | true           |
    And there are projects:
      | id | name        | owned by | downloads | private | visible | auto_hidden |
      | 1  | popular     | Catrobat | 30        | false   | true    | false       |
      | 2  | rare        | Catrobat | 10        | false   | true    | false       |
      | 3  | tie low id  | Catrobat | 20        | false   | true    | false       |
      | 4  | tie high id | Catrobat | 20        | false   | true    | false       |
      | 5  | private     | Catrobat | 100       | true    | true    | false       |
      | 6  | invisible   | Catrobat | 100       | false   | false   | false       |
      | 7  | auto hidden | Catrobat | 100       | false   | true    | true        |
      | 8  | banned      | Banned   | 100       | false   | true    | false       |
      | 9  | other brick | Catrobat | 100       | false   | true    | false       |
    And there are project code statistics:
      | project_id | brick_counts            |
      | 1          | SetXBrick:2,WaitBrick:1 |
      | 2          | SetXBrick:1             |
      | 3          | SetXBrick:1             |
      | 4          | SetXBrick:3             |
      | 5          | SetXBrick:1             |
      | 6          | SetXBrick:1             |
      | 7          | SetXBrick:1             |
      | 8          | SetXBrick:1             |
      | 9          | WaitBrick:4             |
    And I have a request header "HTTP_ACCEPT" with value "application/json"

  Scenario: Only public, visible projects using the brick, most downloaded first
    When I request "GET" "/api/docs/reference/bricks/SetXBrick/projects"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name        |
      | popular     |
      | tie high id |
      | tie low id  |
      | rare        |
    And the JSON response field "data.0.name" should be '"popular"'
    And the JSON response field "data.1.name" should be '"tie high id"'
    And the JSON response field "data.2.name" should be '"tie low id"'
    And the JSON response field "data.3.name" should be '"rare"'

  Scenario: Cursor pagination continues after the sort key and the id
    When I request "GET" "/api/docs/reference/bricks/SetXBrick/projects?limit=2"
    Then the response status code should be "200"
    And the response should contain projects in the following order:
      | Name        |
      | popular     |
      | tie high id |
    And I save the next_cursor from the response
    When I request page 2 with the saved cursor at "/api/docs/reference/bricks/SetXBrick/projects?limit=2"
    Then the response status code should be "200"
    And the response should contain projects in the following order:
      | Name       |
      | tie low id |
      | rare       |
    And the JSON response field "data.0.name" should be '"tie low id"'
    And the JSON response field "has_more" should be "false"

  Scenario: A brick nobody uses has no examples
    When I request "GET" "/api/docs/reference/bricks/UnusedBrick/projects"
    Then the response status code should be "200"
    And the JSON response field "data" should be "[]"

  Scenario: An invalid cursor is a bad request
    When I request "GET" "/api/docs/reference/bricks/SetXBrick/projects?cursor=not-a-cursor"
    Then the response status code should be "400"
