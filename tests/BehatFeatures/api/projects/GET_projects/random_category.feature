@api @projects
Feature: Get random projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are projects:
      | id | name      | owned by | views | downloads | upload time      | FileSize | version | language version | flavor     | upload_language | visible | rand |
      | 1  | project 1 | Catrobat | 10    | 10        | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | en              | true    |    0 |
      | 2  | project 2 | User1    | 50    | 5         | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | fr              | true    |    0 |
      | 3  | project 3 | Catrobat | 50    | 40        | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode | de              | true    |    1 |
      | 4  | project 4 | User2    | 50    | 20        | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | en              | true    |    0 |
      | 5  | project 5 | User1    | 40    | 10        | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | de              | true    |    0 |
      | 6  | project 6 | User1    | 20    | 15        | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       | fr              | true    |    0 |

  Scenario: Get random projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=random"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain the following projects:
      | Name      |
      | project 1 |
      | project 2 |
      | project 3 |
      | project 4 |
      | project 5 |
      | project 6 |

  Scenario: Get random projects rand_idx
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=random&limit=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain the following projects:
      | Name      |
      | project 3 |

  Scenario: Get random projects with flavor = luna
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=random&flavor=luna"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain the following projects:
      | Name      |
      | project 2 |
      | project 4 |
      | project 6 |
