@api @search
Feature: Search projects and users

  Background:
    Given there are users:
      | name     | id |
      | Catrobat | 1  |
      | User1    | 2  |
      | NewUser  | 3  |
      | Fritz    | 4  |
    And there are projects:
      | id | name             | description | owned by | downloads | views | upload time      | version |
      | 1  | Galaxy War       | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Minions          |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | Fisch            |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Ponny            | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | MarkoTheBest     |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Whack the Marko  | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
      | 7  | Superponny Fritz | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
      | 8  | Universe         |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | Webteam          |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | Fritz the Cat    |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"
    And the server name is "pocketcode.org"
    And I wait for the search index to be updated

  Scenario: Search for a project without query does not work
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search"
    Then the response code should be "400"

  Scenario: Search for a project / user using the query parameter
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Fritz"
    Then the response code should be "200"
    Then the search response should contain 2 projects
    Then the search response should contain 1 users
    Then the search response should contain the following projects:
      | Name             |
      | Superponny Fritz |
      | Fritz the Cat    |
    And the search response should contain the following users:
      | Name  |
      | Fritz |

  Scenario: Search for a project / user using the query parameter can also have pagination
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Fritz&limit=1&offset=0"
    Then the response code should be "200"
    Then the search response should contain 1 projects
    Then the search response should contain 1 users

  Scenario: Search for a project /user can result in no results
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=NOTHING_TO_FIND"
    Then the response code should be "200"
    Then the search response should contain 0 projects
    Then the search response should contain 0 users

  Scenario: Search only for projects
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Fritz&type=projects"
    Then the response code should be "200"
    Then the search response should not contain any "users"
    Then the search response should contain 2 projects
    Then the search response should contain the following projects:
      | Name             |
      | Superponny Fritz |
      | Fritz the Cat    |

  Scenario: Search only for users
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Fritz&type=users"
    Then the response code should be "200"
    Then the search response should not contain any "projects"
    Then the search response should contain 1 users
    Then the search response should contain the following users:
      | Name  |
      | Fritz |

  Scenario: Search for a invalid type
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Fritz&type=invalid"
    Then the response code should be "400"
