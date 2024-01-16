@api @projects @disabled
Feature: Get example projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are projects:
      | id | name      | owned by | views | downloads | upload time      | FileSize | version | language version | flavor     | upload_language |
      | 1  | project 1 | Catrobat | 10    | 10        | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | en              |
      | 2  | project 2 | User1    | 50    | 5         | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | fr              |
      | 3  | project 3 | Catrobat | 50    | 40        | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode | de              |
      | 4  | project 4 | User2    | 50    | 20        | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | en              |
      | 5  | project 5 | User1    | 40    | 10        | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | de              |
      | 6  | project 6 | User1    | 20    | 15        | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       | fr              |
    And there are flavors:
      | id | name       |
      | 1  | arduino    |
      | 2  | embroidery |
    And following projects are examples:
      | name      | active | priority | flavor     |
      | project 1 | 0      | 1        | arduino    |
      | project 2 | 1      | 2        | embroidery |
      | project 3 | 1      | 3        | embroidery |


  Scenario: Get example projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=example"
    Then the response status code should be "200"
    Then the response should contain example projects in the following order:
      | Name      |
      | project 3 |
      | project 2 |
