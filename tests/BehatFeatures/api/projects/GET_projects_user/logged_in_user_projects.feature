@api @projects
Feature: Logged in user projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are projects:
      | id | name       | owned by | views | upload time      | FileSize | version | language version | flavor     | private |
      | 1  | project 1  | Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | false   |
      | 2  | project 2  | User1    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | pocketcode | true    |
      | 3  | project 3  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode | true    |
      | 4  | project 4  | User2    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | pocketcode | true    |
      | 5  | project 5  | User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 6  | project 6  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 7  | project 7  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 8  | project 8  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 9  | project 9  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 10 | project 10 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 11 | project 11 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 12 | project 12 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 13 | project 13 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 14 | project 14 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 15 | project 15 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 16 | project 16 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 17 | project 17 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 18 | project 18 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 19 | project 19 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 20 | project 20 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 21 | project 21 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 22 | project 22 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 23 | project 23 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 24 | project 24 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 25 | project 25 | Catrobat | 40    | 01.08.2014 12:30 | 1048576  | 0.8.5   | 0.985            | pocketcode | true    |
      | 26 | project 26 | Catrobat | 40    | 01.08.2014 14:00 | 1048576  | 0.8.5   | 0.985            | luna       | true    |


  Scenario: Get projects without being logged in
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "GET" "/api/projects/user"
    Then the response status code should be "401"
    And I should get the json object:
    """
      {
        "code": 401,
        "message": "JWT Token not found"
      }
    """

  Scenario: Get logged in user projects (newest first)
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user?limit=2"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name       |
      | project 26 |
      | project 25 |

  Scenario: Get logged in user projects with limit = 1 and offset = 0
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?limit=1&offset=0"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name       |
      | project 26 |

  Scenario: Get logged in user projects with limit = 1 and offset = 1
    Given I use a valid JWT Bearer token for "User1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?limit=1&offset=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 5 |

  Scenario: Get logged in user projects with offset = 1
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?offset=1"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then I should get the json object:
      """
      []
      """

  Scenario: Get logged in user projects with maxVersion = 0.984
    Given I use a valid JWT Bearer token for "User1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?max_version=0.984"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 2 |

  Scenario: Get logged in user projects with flavor = luna
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?flavor=luna"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name       |
      | project 26 |

  Scenario: Get logged in user projects with flavor = luna and specific attributes
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "flavor" with value "luna"
    And I have a parameter "attributes" with value "name,flavor,private"
    And I request "GET" "/api/projects/user/"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
          "name": "project 26",
          "private": true,
          "flavor": "luna"
        }
      ]
      """

  Scenario: Get logged in user projects with the default limit
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain 20 projects

