@api
Feature: List languages with defined custom translation

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |
    And there are project custom translations:
      | project_id | language | name    | description    | credit    |
      | 1          | fr       | fr_name | fr_description | fr_credit |
      | 1          | en       | en_name |                |           |
      | 1          | es       |         | es_description |           |
      | 1          | de       |         |                | de_credit |

  Scenario: List languages with custom translations for any of name, description and credit
    When I request "GET" "/app/translate/custom/project/1/list"
    Then the response code should be "200"
    And the response should be in json format
    And the client response should contain "fr"
    And the client response should contain "en"
    And the client response should contain "es"
    And the client response should contain "de"
