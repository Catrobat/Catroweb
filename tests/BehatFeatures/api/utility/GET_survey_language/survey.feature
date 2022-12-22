@api @utility
Feature: There must be a simple way to check the status/health of the catroweb API/services

  Background:
    Given there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | embroidery |
    And there are surveys:
      | language code | url                  | platform | flavor     |
      | en            | www.catrosurvey.com  |          |            |
      | de            | www.catrosurvey.at   |          |            |
      | fr            | www.catrosurvey1.fr  |          |            |
      | fr            | www.catrosurvey2.fr  | ios      |            |
      | fr            | www.catrosurvey3.fr  |          | pocketcode |
      | fr            | www.catrosurvey4.fr  | ios      | embroidery |
      | fr            | www.catrosurvey5.fr  | android  | embroidery |

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

  Scenario: A survey request returns the correct survey (platform, flavor is optional)
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

  Scenario: A survey request returns the first survey to fit the criteria - language
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey1.fr" }
    """

  Scenario: A survey request returns the first survey to fit the criteria - language, platform
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr?platform=ios"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey2.fr" }
    """

  Scenario: A survey request returns the first survey to fit the criteria - language, flavor
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr?flavor=pocketcode"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey3.fr" }
    """

  Scenario: A survey request returns the first survey to fit the criteria - language, flavor
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr?flavor=embroidery"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey4.fr" }
    """

  Scenario: A survey request returns the first survey to fit the criteria - language, flavor, platform
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr?flavor=embroidery&platform=android"
    Then the response status code should be "200"
    Then the response should have the survey model structure
    Then I should get the json object:
    """
      { "url": "www.catrosurvey5.fr" }
    """

  Scenario: A survey request with invalid flavor is a bad request
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr?flavor=invalid"
    Then the response status code should be "400"

  Scenario: a survey request with invalid platform is a bad request
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/survey/fr?platform=invalid"
    Then the response status code should be "400"