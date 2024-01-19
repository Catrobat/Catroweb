@api @projects
Feature: Get project

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

  Scenario: Get specific project
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/1"
    Then the response status code should be "200"
    Then the response should have the project model structure
    Then the response should contain the following project:
      | Name      |
      | project 1 |


  Scenario: Get specific project
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2"
    Then the response status code should be "200"
    Then the response should have the project model structure
    Then the response should contain the following project:
      | Name      |
      | project 2 |

  Scenario: Accessing private project must be possible
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/3"
    Then the response status code should be "200"
    Then the response should have the project model structure
    Then the response should contain the following project:
      | Name      |
      | project 3 |

  Scenario: Accessing hidden project must be not possible
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/4"
    Then the response status code should be "404"
    Then the response content must be empty

  Scenario: Get specific project with no existing id
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/5"
    Then the response status code should be "404"
    Then the response content must be empty

  Scenario: Get specific project without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/project/1"
    Then the response status code should be "406"
