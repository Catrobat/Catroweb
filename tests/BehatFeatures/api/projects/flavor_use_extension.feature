@api @projects
Feature: The extension should be used in addition to the flavor to find projects in the homepage categories

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are extensions:
      | id | internal_title |
      | 1  | embroidery     |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
      | 3  | embroidery |
    And there are projects:
      | id | name      | extensions | flavor     |
      | 1  | project 1 | embroidery | pocketcode |
      | 2  | project 2 | embroidery | embroidery |
      | 3  | project 3 |            | pocketcode |
      | 4  | project 4 |            | embroidery |
      | 5  | project 5 |            | luna       |

  Scenario: Get recent projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=recent&flavor=embroidery"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 1 |
      | project 2 |
      | project 4 |

  Scenario: Get recent projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=most_downloaded&flavor=embroidery"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 1 |
      | project 2 |
      | project 4 |

  Scenario: Get recent projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?category=most_viewed&flavor=embroidery"
    Then the response status code should be "200"
    Then the response should have the default projects model structure
    Then the response should contain projects in the following order:
      | Name      |
      | project 1 |
      | project 2 |
      | project 4 |
