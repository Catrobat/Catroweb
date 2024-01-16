@api
Feature: Comment translation should be cached

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |
    And there are comments:
      | id | project_id | user_id | text |
      | 1  | 1          | 1       | c1   |

  Scenario: Comment translation should include etag
    Given I request "GET" "/app/translate/comment/1?target_language=fr"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"a9f7e97965d6cf799a529102a973b8b9fr"'

  Scenario: Comment translation should be cached when comment is not modified
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"a9f7e97965d6cf799a529102a973b8b9fr"'
    And I request "GET" "/app/translate/comment/1?target_language=fr"
    Then the response status code should be "304"

  Scenario: Comment translation should not be cached when comment is changed
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"different value"'
    And I request "GET" "/app/translate/comment/1?target_language=fr"
    Then the response status code should be "200"

  Scenario: Comment translation should not be cached when target language changes
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"a9f7e97965d6cf799a529102a973b8b9fr"'
    And I request "GET" "/app/translate/comment/1?target_language=es"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"a9f7e97965d6cf799a529102a973b8b9es"'
