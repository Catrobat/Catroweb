@api @search
Feature: Search projects by name, description, or ID

  Background:
    Given there are users:
      | name     | id |
      | Catrobat | 1  |
      | User1    | 2  |
      | NewUser  | 3  |
    And there are projects:
      | id        | name            | description               | owned by | upload time      |
      | qysm-rhwt | Galaxy War      | description1              | User1    | 01.01.2014 12:00 |
      | phci-etqx | Minions         |                           | Catrobat | 02.02.2014 14:00 |
      | bbns-hixd | Fisch           |                           | User1    | 10.01.2012 14:00 |
      | rppk-kkri | Ponny           | description2              | User1    | 09.01.2012 14:00 |
      | nhre-xzvg | MarkoTheBest    |                           | NewUser  | 08.01.2012 14:00 |
      | ydmf-tbms | Whack the Marko | Universe                  | Catrobat | 07.02.2012 14:00 |
      | anxu-nsss | Superponny      | description1 description2 | User1    | 06.01.2012 14:00 |
      | kbrw-khwf | Universe        |                           | NewUser  | 05.01.2012 14:00 |
      | isxs-adkt | Webteam         |                           | NewUser  | 04.01.2012 14:00 |
      | tvut-irkw | Fritz the Cat   |                           | NewUser  | 03.01.2012 14:00 |
    And the current time is "01.08.2014 14:00"
    And I wait for the search index to be updated

  Scenario: Search by name
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Minions&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Minions |

  Scenario: Search by keyword in description
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=description1&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name       |
      | Galaxy War |
      | Superponny |

  Scenario: Search by another keyword in description
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=description2&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name       |
      | Ponny      |
      | Superponny |

  Scenario: Search with AND logic in query
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=description1 description2&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name       |
      | Superponny |

  Scenario: Match in name prioritized over description
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=Universe&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name            |
      | Universe        |
      | Whack the Marko |

  Scenario: Search by partial ID (prefix)
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=kbrw&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name     |
      | Universe |

  Scenario: Search by partial ID (mid)
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=phci&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Minions |

  Scenario: Search by partial ID (suffix)
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=-etqx&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name    |
      | Minions |

  Scenario: Hidden projects should not be listed
    Given project "Ponny" is not visible
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=description2&type=projects"
    Then the response code should be "200"
    Then the search response should contain the following projects:
      | Name       |
      | Superponny |

  Scenario: Search for nonexistent term returns no results
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/search?query=NOTHINGTOBEFIOUND&type=projects"
    Then the response code should be "200"
    Then the search response should contain 0 projects
