@api
Feature: GET /api/translation/languages lists the languages the machine translation offers

  Scenario: Anyone gets the language codes, as the translate endpoints take them
    Given I request "GET" "/api/translation/languages"
    Then the response status code should be "200"
    And the JSON response field "0" should be '"de"'
    And the JSON response field "4" should be '"zh-CN"'
