@api @projects
Feature: Get recent projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | upload time      | FileSize | version | language version |   flavor    | upload_language |
      | 1  | project 1 |  Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |  pocketcode |      en         |
      | 2  | project 2 |  User1    | 50    | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      fr         |
      | 3  | project 3 |  Catrobat | 40    | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |  pocketcode |      de         |
      | 4  | project 4 |  User2    | 50    | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      en         |
      | 5  | project 5 |  User1    | 40    | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |      de         |
      | 6  | project 6 |  User1    | 50    | 02.08.2014 13:00 | 1048576  | 0.8.5   | 0.985            |  luna       |      fr         |


  Scenario: Get recent projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recent"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 5 |
      | project 4 |
      | project 3 |
      | project 6 |
      | project 2 |
      | project 1 |

  Scenario: Get recent projects in german and limit = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de"
    And I request "GET" "/api/projects/?category=recent&limit=1"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 5 |

  Scenario: Get recent projects in english with offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "en"
    And I request "GET" "/api/projects/?category=recent&offset=1"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 4 |
      | project 3 |
      | project 6 |
      | project 2 |
      | project 1 |

  Scenario: Get recent projects in french with max_version = 0.982
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "fr"
    And I request "GET" "/api/projects/?category=recent&max_version=0.982"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 3 |

  Scenario: Get recent projects with flavor = luna
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recent&flavor=luna"
    Then the response status code should be "200"
    Then the response should have the projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 4 |
      | project 6 |
      | project 2 |
