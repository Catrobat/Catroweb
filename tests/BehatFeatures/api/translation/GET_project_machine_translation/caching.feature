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
    Given I request "GET" "/api/projects/1/translation?target_language=fr"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"b4e53da92e1cb7e7d7a5a288bff1449bautofr"'

  Scenario: Project translation should be cached when project is not modified
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"b4e53da92e1cb7e7d7a5a288bff1449bautofr"'
    And I request "GET" "/api/projects/1/translation?target_language=fr"
    Then the response status code should be "304"

  Scenario: Project translation should not be cached when project is changed
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"different value"'
    And I request "GET" "/api/projects/1/translation?target_language=fr"
    Then the response status code should be "200"

  Scenario: Project translation should not be cached when target language changes
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"b4e53da92e1cb7e7d7a5a288bff1449bautofr"'
    And I request "GET" "/api/projects/1/translation?target_language=es"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"b4e53da92e1cb7e7d7a5a288bff1449bautoes"'

  Scenario: Project translation should not be cached when the source language changes
    Given I have a request header "HTTP_IF_NONE_MATCH" with value '"b4e53da92e1cb7e7d7a5a288bff1449bautofr"'
    And I request "GET" "/api/projects/1/translation?target_language=fr&source_language=de"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"b4e53da92e1cb7e7d7a5a288bff1449bdefr"'
