@api
Feature: GET /api/featured-banners only returns banners targeted at the request locale, plus global ones

  Background:
    Given there are featured banners:
      | url                   | title          | active | priority | type | locales |
      | https://global.test/  | Global banner  | 1      | 4        | link |         |
      | https://german.test/  | German banner  | 1      | 3        | link | de      |
      | https://austria.test/ | Austria banner | 1      | 2        | link | de_AT   |
      | https://french.test/  | French banner  | 1      | 1        | link | fr,it   |

  Scenario: Without Accept-Language (defaults to en) only global banners are returned
    When I request "GET" "/api/featured-banners"
    Then the response status code should be "200"
    And the client response should contain "Global banner"
    And the client response should not contain "German banner"
    And the client response should not contain "Austria banner"
    And the client response should not contain "French banner"

  Scenario: A language-wide banner matches every region of that language
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de-DE,de;q=0.9"
    When I request "GET" "/api/featured-banners"
    Then the response status code should be "200"
    And the client response should contain "Global banner"
    And the client response should contain "German banner"
    And the client response should not contain "Austria banner"
    And the client response should not contain "French banner"

  Scenario: A region-specific banner matches only that region
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de-AT"
    When I request "GET" "/api/featured-banners"
    Then the response status code should be "200"
    And the client response should contain "Global banner"
    And the client response should contain "German banner"
    And the client response should contain "Austria banner"
    And the client response should not contain "French banner"

  Scenario: A banner with several locales matches each of them
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "it"
    When I request "GET" "/api/featured-banners"
    Then the response status code should be "200"
    And the client response should contain "Global banner"
    And the client response should contain "French banner"
    And the client response should not contain "German banner"

  Scenario: A locale without targeted banners falls back to the global ones
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "ja"
    When I request "GET" "/api/featured-banners"
    Then the response status code should be "200"
    And the client response should contain "Global banner"
    And the client response should not contain "German banner"
