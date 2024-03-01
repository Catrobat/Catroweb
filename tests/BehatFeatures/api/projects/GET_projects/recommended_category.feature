@api @disabled
Feature: Get recommended programs on homepage

  To find yet unliked programs that were liked by similar users.
  Similar users are users that liked some of the same programs that the current user also liked
  (user-based Collaborative Filtering using Jaccard distance as similarity measure).

  Background:
    Given there are users:
      | id | name      | password | token      | id |
      | 1  | Catrobat1 | 12345    | cccccccccc | 1  |
      | 2  | Catrobat2 | 12345    | cccccccccc | 2  |
      | 3  | Catrobat3 | 12345    | cccccccccc | 3  |
      | 4  | Catrobat4 | 12345    | cccccccccc | 4  |
    And there are projects:
      | id | name    | description | owned by  | downloads | views | upload time      | version | language version | flavor     |
      | 1  | Game    | p4          | Catrobat4 | 5         | 1     | 01.03.2013 12:00 | 0.8.5   | 0.985            | pocketcode |
      | 2  | Minions | p1          | Catrobat1 | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | 0.985            | pocketcode |
      | 3  | Galaxy  | p2          | Catrobat2 | 10        | 13    | 01.02.2013 12:00 | 0.8.5   | 0.985            | pocketcode |
      | 4  | Other   | p3          | Catrobat3 | 12        | 9     | 01.02.2013 12:00 | 0.8.5   | 0.985            | pocketcode |
      | 5  | Other2  | p5          | Catrobat2 | 3         | 9     | 01.02.2013 12:00 | 0.8.5   | 0.999            | pocketcode |
      | 6  | Other3  | p6          | Catrobat1 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | 0.985            | pocketcode |
      | 7  | Other4  | p7          | Catrobat4 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | 0.985            | luna       |
      | 8  | Other5  | p7          | Catrobat3 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | 0.999            | luna       |
      | 9  | Other6  | p7          | Catrobat2 | 1         | 9     | 01.02.2013 12:00 | 0.8.5   | 0.999            | pocketcode |
    And there are likes:
      | username  | project_id | type | created at       |
      | Catrobat1 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat1 | 2          | 2    | 01.01.2017 12:00 |
      | Catrobat2 | 1          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 2          | 3    | 01.01.2017 12:00 |
      | Catrobat2 | 5          | 3    | 01.01.2017 12:00 |
      | Catrobat1 | 5          | 3    | 01.01.2017 12:00 |
      | Catrobat3 | 5          | 3    | 01.01.2017 12:00 |
      | Catrobat3 | 1          | 3    | 01.01.2017 12:00 |
      | Catrobat3 | 2          | 3    | 01.01.2017 12:00 |
      | Catrobat3 | 9          | 3    | 01.01.2017 12:00 |
      | Catrobat2 | 9          | 1    | 01.01.2017 12:00 |
      | Catrobat1 | 4          | 1    | 01.01.2017 12:00 |
      | Catrobat2 | 4          | 1    | 01.01.2017 12:00 |
      | Catrobat1 | 7          | 2    | 01.01.2017 12:00 |
      | Catrobat2 | 7          | 2    | 01.01.2017 12:00 |

  Scenario: Get recommended projects with invalid parameter should return 400
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended&limit=nolimit"
    Then the response status code should be "400"

  Scenario: Get recommended projects without application/json content type should return 406
    And I request "GET" "/api/projects/?category=recommended&limit=10"
    Then the response status code should be "406"

  Scenario: Get recommended projects
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name    |
      | Game    |
      | Minions |
      | Other2  |
      | Other4  |
      | Other6  |
      | Other   |

  Scenario: Get recommended projects with limit 2
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended&limit=2"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name    |
      | Game    |
      | Minions |


  Scenario: Get recommended projects with offset 2
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended&offset=2"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name   |
      | Other2 |
      | Other4 |
      | Other6 |
      | Other  |

  Scenario: Get recommended projects with limit 2 and offset 2
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended&limit=2&offset=2"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name   |
      | Other2 |
      | Other  |


  Scenario: Get recommended projects with luna flavor
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended&flavor=luna"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name   |
      | Other4 |

  Scenario: Get recommended projects with max version 0.985
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recommended&max_version=0.985"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name    |
      | Game    |
      | Minions |
      | Other   |
      | Other4  |
