@api
Feature: Download programs

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
    And there are downloadable projects:
      | id | name      | description | owned by | downloads | views | upload time      | version | visible | private |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | true    | false   |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | false   | false   |
      | 3  | program 3 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   | true    | true    |

  Scenario: Projects can be downloaded
    When I request "GET" "/api/project/1/catrobat"
    Then i should receive a project file
    And the response code should be "200"

  Scenario: Invisible projects can't be downloaded
    When I request "GET" "/api/project/2/catrobat"
    Then the response code should be "404"

  Scenario: Private projects can be downloaded!
    When I request "GET" "/api/project/3/catrobat"
    Then the response code should be "200"

  Scenario: Projects can't be downloaded if they do not exist
    When I request "GET" "/api/project/999/catrobat"
    Then the response code should be "404"

  Scenario: Projects download counter increases not when not authenticated
    When I request "GET" "/api/project/1/catrobat"
    Then i should receive a project file
    And the response code should be "200"
    Then the project "1" should have "3" downloads

  Scenario: Projects download counter increases only once
    Given I use a valid JWT Bearer token for "Catrobat"
    When I request "GET" "/api/project/1/catrobat"
    Then i should receive a project file
    And the response code should be "200"
    Then the project "1" should have "4" downloads
    When I request "GET" "/api/project/1/catrobat"
    Then i should receive a project file
    And the response code should be "200"
    Then the project "1" should have "4" downloads
