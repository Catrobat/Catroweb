@api @search @extensions
Feature: Search projects using their extensions

  To find projects, users should be able to search all available projects for specific words and extensions

  Background:
    Given there are users:
      | name     | id |
      | Catrobat | 1  |
      | User1    | 2  |
    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    And there are projects:
      | id | name    | description | owned by | extensions       |
      | 1  | Minions | p1          | Catrobat | mindstorms,phiro |
      | 2  | Galaxy  | p2          | User1    | mindstorms,drone |
      | 3  | Alone   | p3          | User1    |                  |
    And I wait for the search index to be updated

  Scenario: Search projects by extension with parameters
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I GET "/api/search?query=phiro" with these parameters
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Minions |

  Scenario: Search by keyword matching extension (mindstorms)
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I GET "/api/search?query=mindstorms" with these parameters
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Galaxy  |
      | Minions |
