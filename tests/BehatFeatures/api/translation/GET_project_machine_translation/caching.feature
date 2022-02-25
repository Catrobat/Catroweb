@api
Feature: Project translation should be cached

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |

  Scenario: Project translation should include etag
    Given I request "GET" "/app/translate/project/1?target_language=fr"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"c7e50946e88cbd9d1d5989809aefcb84fr"'

  Scenario: Project translation should be cached when project is not modified
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"c7e50946e88cbd9d1d5989809aefcb84fr"'
    And I request "GET" "/app/translate/project/1?target_language=fr"
    Then the response status code should be "304"

  Scenario: Project translation should not be cached when project is changed
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"different value"'
    And I request "GET" "/app/translate/project/1?target_language=fr"
    Then the response status code should be "200"

  Scenario: Project translation should not be cached when target language changes
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"c7e50946e88cbd9d1d5989809aefcb84fr"'
    And I request "GET" "/app/translate/project/1?target_language=es"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"c7e50946e88cbd9d1d5989809aefcb84es"'
