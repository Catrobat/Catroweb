@api
Feature: Project machine translation failure modes

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |

  Scenario: Translation of non-existent project returns 404
    Given I request "GET" "/api/projects/nonexistent/translation?target_language=fr"
    Then the response status code should be "404"

  Scenario: Same source and target language returns 422
    Given I request "GET" "/api/projects/1/translation?target_language=fr&source_language=fr"
    Then the response status code should be "422"

  Scenario: Missing target_language parameter returns 400
    Given I request "GET" "/api/projects/1/translation"
    Then the response status code should be "400"
