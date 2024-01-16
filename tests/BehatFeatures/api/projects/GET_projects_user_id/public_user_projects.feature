@api @projects
Feature: User public projects

  Background:
    Given there are users:
      | id     | name     | password |
      | user-1 | Catrobat | 123456   |
      | user-2 | User1    | 123456   |
      | user-3 | User2    | 123456   |
      | user-4 | User3    | 123456   |
    And there are projects:
      | id | name       | owned by | views | upload time      | FileSize | version | language version | flavor     | private | visible |
      | 1  | project 1  | Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | false   | true    |
      | 2  | project 2  | User1    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | false   | true    |
      | 3  | project 3  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            | luna       | false   | true    |
      | 4  | project 4  | User2    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | pocketcode | true    | true    |
      | 5  | project 5  | User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       | false   | true    |
      | 6  | project 6  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | false   | true    |
      | 7  | project 7  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | false   | true    |
      | 8  | project 8  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | false   | false   |
      | 9  | project 9  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | false   | true    |
      | 10 | project 10 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | false   | true    |


  Scenario: Get user public projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-1/"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name       |
      | project 1  |
      | project 10 |
      | project 3  |
      | project 6  |
      | project 7  |
      | project 9  |

  Scenario: Get user public projects with limit = 1 and offset = 0
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-1/?limit=1&offset=0"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 1 |

  Scenario: Get user public projects with limit = 1 and offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-2/?limit=1&offset=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 5 |

  Scenario: Get user public projects with offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-3/?offset=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then I should get the json object:
      """
      []
      """

  Scenario: Get user public projects with maxVersion = 0.984
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-2/?max_version=0.984"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 2 |

  Scenario: Get user public projects with maxVersion = 0.984 and specific attributes
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "max_version" with value "0.984"
    And I have a parameter "attributes" with value "id,name,author,views,flavor"
    And I request "GET" "/api/projects/user/user-2/"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
          "id": "2",
          "name": "project 2",
          "author": "User1",
          "views": 50,
          "flavor": "luna"
        }
      ]
      """

  Scenario: Get user public projects with flavor = luna
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-1/?flavor=luna"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 3 |
