@api @utility
Feature: There must be a simple way to check the status/health of the catroweb API/services

  Scenario: A health request returns status code 204 if services are alive
    When I request "GET" "/api/health"
    Then the response status code should be "204"
    And the response content must be empty