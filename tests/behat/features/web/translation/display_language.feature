@web
Feature: display language list should be returned in user's language

  Scenario: When the user has no preferred language, language names should be in English
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name            |
      | en-US         | English (United States) |
      | fr-FR         | French (France)         |

  Scenario: When the user uses French, languages names should be in French
    Given I set request language to "French"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name         |
      | en-US         | anglais (États-Unis) |
      | fr-FR         | français (France)    |

  Scenario: When the user uses German, languages names should be in German
    Given I set request language to "Deutsch"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name                  |
      | en-US         | Englisch (Vereinigte Staaten) |
      | fr-FR         | Französisch (Frankreich)      |
