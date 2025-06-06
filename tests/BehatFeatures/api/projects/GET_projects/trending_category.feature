@api @projects
Feature: Get trending projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And the current time is "03.07.2012 12:00"
    And there are projects:
      | id | name      | owned by | views | downloads | upload time      | FileSize | version | language version | flavor     | upload_language |
      | 1  | project 1 | Catrobat | 10    | 10        | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            | pocketcode | en              |
      | 2  | project 2 | User1    | 50    | 5         | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | fr              |
      | 3  | project 3 | Catrobat | 50    | 40        | 03.08.2099 12:00 | 1048576  | 0.8.5   | 0.123            | pocketcode | de              |
      | 4  | project 4 | User2    | 50    | 20        | 04.08.2099 12:00 | 1048576  | 0.8.5   | 0.984            | luna       | en              |
      | 5  | project 5 | User1    | 40    | 10        | 05.08.2099 12:00 | 1048576  | 0.8.5   | 0.985            | pocketcode | de              |
      | 6  | project 6 | User1    | 20    | 15        | 02.08.2099 12:00 | 1048576  | 0.8.5   | 0.982            | luna       | fr              |


  Scenario: Get trending projects
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=trending"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name      |
      | project 3 |
      | project 4 |
      | project 6 |
      | project 5 |

  Scenario: Get trending projects in german and limit = 1
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I request "GET" "/api/projects/?category=trending&limit=1"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name      |
      | project 3 |

  Scenario: Get trending projects in english with offset = 1
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "en"
    And I request "GET" "/api/projects/?category=trending&offset=1"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name      |
      | project 4 |
      | project 6 |
      | project 5 |

  Scenario: Get trending projects in french with max_version = 0.982
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "fr_FR"
    And I request "GET" "/api/projects/?category=trending&max_version=0.982"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name      |
      | project 3 |
      | project 6 |

  Scenario: Get trending projects with flavor = luna
    When I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=trending&flavor=luna"
    Then the response status code should be "200"
    And the response should have the default projects model structure
    And the response should contain projects in the following order:
      | Name      |
      | project 4 |
      | project 6 |
