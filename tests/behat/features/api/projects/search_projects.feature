@api @projects
Feature: Search projects

  To find programs, users should be able to search all available programs for specific words

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser  | 54321    | bbbbbbbbbb | 3  |
    And there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |
    And there are programs:
      | id        | name            | description               | owned by | extensions  |upload time      | version | private | visible |
      | qysm-rhwt | Galaxy War      | description1              | User1    | Arduino     |01.01.2014 12:00 | 0.8.5   | false   | true    |
      | phci-etqx | Minions         |                           | Catrobat | Drone       |02.02.2014 14:00 | 0.8.5   | false   | true    |
      | bbns-hixd | Fisch           |                           | User1    | Lego        |10.01.2012 14:00 | 0.8.5   | true    | true    |
      | rppk-kkri | Ponny           | description2              | User1    | Phiro       |09.01.2012 14:00 | 0.8.5   | false   | false   |
      | nhre-xzvg | MarkoTheBest    |                           | NewUser  | Raspberry Pi|08.01.2012 14:00 | 0.8.5   | false   | true    |
      | ydmf-tbms | Whack the Marko | p                         | Catrobat | Drone,Lego  |01.02.2012 14:00 | 0.8.5   | false   | true    |
      | anxu-nsss | Superponny      | description1 description2 | User1    | Lego        |06.01.2012 14:00 | 0.8.5   | false   | true    |
      | kbrw-khwf | ponny           |                           | NewUser  | Arduino     |05.01.2012 14:00 | 0.8.5   | false   | true    |
      | isxs-adkt | Webteam         |                           | NewUser  | Lego        |04.01.2012 14:00 | 0.8.5   | false   | true    |
      | tvut-irkw | Fritz the Cat   |                           | NewUser  | Lego        |03.01.2012 14:00 | 0.8.5   | false   | true    |

  Scenario: Search for projects with specific word

    Given I have a parameter "query_string" with value "Galaxy"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain total projects with value 1
    Then the response should contain projects in the following order:
      | Name      |
      | Galaxy War|

  Scenario: Search for projects with specific user

    Given I have a parameter "query_string" with value "User1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain total projects with value 2
    Then the response should contain projects in the following order:
      | Name      |
      | Superponny|
      | Galaxy War|

  Scenario: Private projects must not appear in the results

    Given I have a parameter "query_string" with value "Fisch"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then I should get the json object:
      """
      {
        "projects": [],
        "total_results": 0
      }
      """

  Scenario: Hidden projects must not appear in the results

    Given I have a parameter "query_string" with value "Ponny description2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then I should get the json object:
      """
      {
        "projects": [],
        "total_results": 0
      }
      """

  Scenario: Search for project using the limit query, total project value must be the overall result count without limit

    Given I have a parameter "query_string" with value "NewUser"
    And I have a parameter "limit" with value "2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain total projects with value 4
    Then the response should contain projects in the following order:
      | Name      |
      | Webteam   |
      | ponny     |

  Scenario: Search for project using the offset query

    Given I have a parameter "query_string" with value "description"
    And I have a parameter "offset" with value "1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain total projects with value 2
    Then the response should contain projects in the following order:
      | Name      |
      | Galaxy War|

  Scenario: Search for project with specific extension

    Given I have a parameter "query_string" with value "Arduino"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/search"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain total projects with value 2
    Then the response should contain projects in the following order:
      | Name      |
      | ponny     |
      | Galaxy War|
