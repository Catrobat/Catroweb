@api @projects
Feature: Get most viewed projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are projects:
      | id | name      | owned by | views | upload time      | FileSize | version | language version | flavor     | upload_language | popularity | visible |
      | 1  | project 1 | Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | en              | 99.0       | true    |
      | 2  | project 2 | User1    | 60    | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.982            | luna       | fr              | 12.5       | false   |
      | 3  | project 3 | Catrobat | 5     | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode | de              | 87.23      | true    |
      | 4  | project 4 | User2    | 50    | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | en              | 0          | true    |
      | 5  | project 5 | User1    | 40    | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | de              | 45.0       | true    |
      | 6  | project 6 | User1    | 20    | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       | fr              | 42.42      | false   |


  Scenario: Get most popular projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=popular"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 1 |
      | project 3 |
      | project 5 |
      | project 4 |

  Scenario: Get most popular projects in german, offset = 1 and limit = 2
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I request "GET" "/api/projects/?category=popular&limit=2&offset=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain the following projects:
    | Name        |
    | project 3   |
    | project 5   |


  Scenario: Get most popular projects in english with offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "en"
    And I request "GET" "/api/projects/?category=popular&offset=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 4 |
      | project 5 |
      | project 3 |

  Scenario: Get most popular projects in french with max_version = 0.982
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "fr_FR"
    And I request "GET" "/api/projects/?category=popular&max_version=0.982"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 3 |

  Scenario: Get most popular projects with flavor = luna
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=popular&flavor=luna"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 4 |
