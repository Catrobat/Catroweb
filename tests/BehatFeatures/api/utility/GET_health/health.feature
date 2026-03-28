@api @utility
Feature: There must be a simple way to check the status/health of the catroweb API/services

  Scenario: A health request returns status code 200 with JSON body
    When I request "GET" "/api/health"
    Then the response status code should be "200"
    And the client response should contain "status"
    And the client response should contain "ok"
