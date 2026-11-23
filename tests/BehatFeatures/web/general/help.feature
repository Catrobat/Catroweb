@web @help
Feature: Help page redirect

  # Note: assert via HTTP redirect (Location header), not browser navigation.

  Scenario Outline: The old help links lead to the docs
    Given I do not follow redirects
    When I set request language to "<language>"
    And I GET "<url>"
    Then the response status code should be "302"
    And the response Location header should be "/app/docs"

    Examples:
      | url              | language |
      | /app/help        | English  |
      | /app/help        | Deutsch  |
      | /app/stepByStep  | English  |
