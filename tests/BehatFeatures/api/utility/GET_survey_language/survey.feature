@api @utility
Feature: There must be a simple way to check the status/health of the catroweb API/services

  Background:
    Given there are surveys:
      | language code | url                 |
      | en            | www.catrosurvey.com |
      | de            | www.catrosurvey.at  |

  Scenario: The request header must be set
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    When I request "GET" "/api/survey/unknown"
    Then the response status code should be "406"
    And the response content must be empty

  Scenario: A survey request can only return a survey if it exists
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/unknown"
    Then the response status code should be "404"
    And the response content must be empty

  Scenario: A survey request returns the correct survey
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/en"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey.com" }
    """

  Scenario: A survey request returns the correct survey 2
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/de"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey.at" }
    """