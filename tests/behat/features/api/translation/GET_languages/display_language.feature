@web
Feature: display language list should be returned in user's language

  Scenario: When the user has no preferred language, language names should be in English
    Given I request "GET" "/app/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name            |
      | en-US         | English (United States) |
      | fr-FR         | French (France)         |

  Scenario: When the user uses French, languages names should be in French
    Given I set request language to "French"
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name         |
      | en-US         | anglais (États-Unis) |
      | fr-FR         | français (France)    |

  Scenario: When the user uses German, languages names should be in German
    Given I set request language to "Deutsch"
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name                  |
      | en-US         | Englisch (Vereinigte Staaten) |
      | fr-FR         | Französisch (Frankreich)      |

  Scenario: Display language list should be cached with etag
    Given I set request language to "French"
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG" with the value '"fr_FR"'

  Scenario: Cached response is valid when it matches user's language
    Given I set request language to "French"
    And I have a request header "HTTP_IF_NONE_MATCH" with value '"fr_FR"'
    And I request "GET" "/app/languages"
    Then the response status code should be "304"

  Scenario: Cached response is not valid when user changes language
    Given I set request language to "French"
    And I have a request header "HTTP_IF_NONE_MATCH" with value '"en"'
    And I request "GET" "/app/languages"
    Then the response status code should be "200"
