@api @projects
Feature: Featured Projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are projects:
      | id | name       | owned by | views | upload time      | FileSize | version | language version | flavor     |
      | 1  | project 1  | Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode |
      | 2  | project 2  | User1    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | pocketcode |
      | 3  | project 3  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode |
      | 4  | project 4  | User2    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | pocketcode |
      | 5  | project 5  | User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 6  | project 6  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 7  | project 7  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 8  | project 8  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 9  | project 9  | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 10 | project 10 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode |
      | 11 | project 11 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 12 | project 12 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | luna       |
      | 13 | project 13 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 14 | project 14 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 15 | project 15 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       |
      | 16 | project 16 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode |
      | 17 | project 17 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       |
      | 18 | project 18 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | luna       |
      | 19 | project 19 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | arduino    |
      | 20 | project 20 | Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | arduino    |
      | 21 | project 21 | User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            | arduino    |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
      | 3  | arduino    |
    And following projects are featured:
      | name       | active | priority | ios_only | flavor     |
      | project 1  | 1      | 1        | no       | pocketcode |
      | project 5  | 1      | 4        | yes      | pocketcode |
      | project 7  | 0      | 2        | yes      | pocketcode |
      | project 10 | 1      | 6        | no       | pocketcode |
      | project 15 | 1      | 3        | yes      | luna       |
      | project 20 | 0      | 2        | yes      | arduino    |
      | project 12 | 1      | 8        | no       | luna       |
      | project 6  | 1      | 2        | no       | pocketcode |
      | project 2  | 1      | 3        | yes      | pocketcode |

  Scenario: Get featured projects with invalid parameter should return 400
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?limit=nolimit"
    Then the response status code should be "400"

  Scenario: Get featured projects without application/json content type should return 406
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/projects/featured/?limit=10"
    Then the response status code should be "406"

  Scenario: Get featured projects with default parameters
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 12 |
      | project 10 |
      | project 5  |
      | project 15 |
      | project 2  |
      | project 6  |
      | project 1  |

  Scenario: Get featured projects with limit 2 and offset 0
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?limit=2&offset=0"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 12 |
      | project 10 |

  Scenario: Get featured projects with limit 2 and offset 2
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?limit=2&offset=2"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 5  |
      | project 15 |

  Scenario: Get featured projects for android platform
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?platform=android"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 12 |
      | project 10 |
      | project 6  |
      | project 1  |

  Scenario: Get featured projects for iOS platform
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?platform=ios"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 5  |
      | project 15 |
      | project 2  |

  Scenario: Get featured projects with luna flavor
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?flavor=luna"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 12 |
      | project 15 |

  Scenario: Get featured projects with max version 0.985
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/featured/?max_version=0.985"
    Then the response status code should be "200"
    Then the response should have the default featured projects model structure
    Then the response should contain featured projects in the following order:
      | Name       |
      | project 5  |
      | project 15 |
      | project 2  |
      | project 6  |

  Scenario: Get featured projects with limit 2, offset 0 and some attributes specified
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    And I have a parameter "attributes" with value "project_id,name,author,url"
    And I request "GET" "/api/projects/featured"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "project_id": "12",
        "url": "http:\/\/localhost\/app\/project\/12",
        "name": "project 12",
        "author": "Catrobat"
      },
      {
        "project_id": "10",
        "url": "http:\/\/localhost\/app\/project\/10",
        "name": "project 10",
        "author": "Catrobat"
      }
    ]
    """