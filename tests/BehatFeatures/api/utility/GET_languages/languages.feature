@api
Feature: GET /api/languages returns available languages localized by Accept-Language

  Scenario: Default language names should be in English
    Given I request "GET" "/api/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name            |
      | en-US         | English (United States) |
      | fr-FR         | French (France)         |

  Scenario: When Accept-Language is French, language names should be in French
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "fr"
    And I request "GET" "/api/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name         |
      | en-US         | anglais (États-Unis) |
      | fr-FR         | français (France)    |

  Scenario: When Accept-Language is German, language names should be in German
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de"
    And I request "GET" "/api/languages"
    Then the response status code should be "200"
    Then the response should have language list structure
    Then the response should contain the following languages:
      | Language Code | Display Name                  |
      | en-US         | Englisch (Vereinigte Staaten) |
      | fr-FR         | Französisch (Frankreich)      |

  Scenario: Response should include ETag header for caching
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "fr"
    And I request "GET" "/api/languages"
    Then the response status code should be "200"
    And the response Header should contain the key "ETAG"

  Scenario: Legacy endpoint redirects to API
    Given I request "GET" "/app/languages"
    Then the response status code should be "301"
