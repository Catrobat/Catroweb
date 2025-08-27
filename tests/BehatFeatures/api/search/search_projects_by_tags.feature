@api @search @tags
Feature: Search projects using their tags

  Background:
    Given there are users:
      | name     | id |
      | Catrobat | 1  |
      | User1    | 2  |
      | Bob      | 3  |
      | NewUser  | 4  |
      | Fritz    | 5  |
      | Frank    | 6  |
      | Emi      | 7  |
      | Judi     | 8  |
      | User2    | 9  |
    And there are tags:
      | internal_title | title_ltm_code |
      | games          | __Spiele       |
      | story          | __Geschichte   |
      | single         | __Allein       |
      | bob            | __Bill         |
      | racing         | __Rennen       |
    And there are projects:
      | id | name          | description | owned by | downloads | views | upload time      | tags         |
      | 1  | Minions       | p1          | Catrobat | 3         | 12    | 01.01.2014 12:00 | games, story |
      | 2  | Galaxy        | p2          | User1    | 10        | 13    | 01.02.2014 12:00 | story        |
      | 3  | Alone         | p3          | User1    | 5         | 1     | 01.03.2014 12:00 | single       |
      | 4  | Ponny         | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | racing       |
      | 9  | Webteam       |             | User1    | 100       | 33    | 01.01.2012 13:00 | bob          |
      | 10 | Fritz the Cat |             | User1    | 112       | 33    | 01.01.2012 13:00 | racing       |
      | 11 | Bobs Game     | dec1        | Fritz    | 4         | 33    | 01.01.2012 12:00 | racing       |
      | 12 | Undertale     | dec1 dec3   | Frank    | 4         | 33    | 01.01.2012 12:00 | racing       |
      | 13 | Pocketmaster  | dec1 dec4   | User2    | 4         | 33    | 01.01.2012 12:00 | racing       |
      | 14 | Clickendemon  | dec2 dec5   | Emi      | 4         | 33    | 01.01.2012 12:00 | racing       |
      | 15 | tap bird      | dec2 dec6   | Bob      | 4         | 33    | 01.01.2012 13:00 | racing       |
      | 16 | Wather        | dec7        | Judi     | 4         | 33    | 01.01.2012 12:00 | bob          |
    And I wait for the search index to be updated

  Scenario: Search projects by tag with parameters
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=games&limit=5&offset=0"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Minions |

  Scenario: Search by keyword matching tag (story)
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Story&limit=10&offset=0"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Minions |
      | Galaxy  |

  Scenario: Search by keyword matching tag (single)
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Single"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name  |
      | Alone |

  Scenario: Keyword AND logic results in no matches
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Bot Game&limit=10&offset=0"
    Then the response code should be "200"
    Then the search response should contain 0 projects

  Scenario: Complex AND logic results in no matches
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Galaxy Ponny Webteam dec1 Single&limit=30&offset=0"
    Then the response code should be "200"
    Then the search response should contain 0 projects
