@api @projects
Feature: Get different projects based on their debug build in the user agent

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And the current time is "03.07.2012 12:00"
    And there are projects:
      | id | name      | owned by | views | downloads | upload time      | FileSize | version | language version | flavor     | upload_language | debug |
      | 1  | project 1 | Catrobat | 10    | 10        | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | en              | true  |
      | 2  | project 2 | User1    | 50    | 5         | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | fr              | false |
      | 3  | project 3 | Catrobat | 50    | 40        | 03.08.2099 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode | de              | true  |
      | 4  | project 4 | User2    | 50    | 20        | 04.08.2099 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | en              | false |
      | 5  | project 5 | User1    | 40    | 10        | 05.08.2099 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | de              | true  |
      | 6  | project 6 | User1    | 20    | 15        | 02.08.2099 12:00 | 1048576  | 0.8.5   | 0.982            | luna       | fr              | false |

  Scenario: Debug projects do not show up in the results - default behavior
    When I use a release catroid build useragent
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects?category=trending"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name      |
      | project 4 |
      | project 6 |

  Scenario: Debug projects do not show up in the results - default behavior
    When I use a debug catroid build useragent
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects?category=trending"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 3 |
      | project 4 |
      | project 6 |
      | project 5 |
