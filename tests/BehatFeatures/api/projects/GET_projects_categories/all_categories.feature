@api @projects
Feature: Get projects oversight

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are projects:
      | id | name      | owned by | views | upload time      | FileSize | version | flavor     | private | visible |
      | 1  | project 1 | Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | pocketcode | false   | true    |
      | 2  | project 2 | User1    | 50    | 02.08.2014 12:00 | 1048576  | 0.8.5   | luna       | false   | true    |
      | 3  | project 3 | Catrobat | 40    | 03.08.2014 12:00 | 1048576  | 0.8.5   | pocketcode | true    | true    |
      | 4  | project 4 | Catrobat | 40    | 03.08.2014 12:00 | 1048576  | 0.8.5   | pocketcode | true    | false   |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
      | 3  | arduino    |
    And following projects are featured:
      | name      | active | priority | ios_only | flavor     |
      | project 1 | 1      | 1        | no       | pocketcode |
      | project 5 | 1      | 4        | yes      | pocketcode |
      | project 7 | 0      | 2        | yes      | pocketcode |

  Scenario: All categories should be returned
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "Accept-Language" with value "de"
    And I request "GET" "/api/projects/categories"
    Then the response status code should be "200"
    And the response should contain all categories

  Scenario: Get response without accept
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/projects/categories"
    Then the response status code should be "406"