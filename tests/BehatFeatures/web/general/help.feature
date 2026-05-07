@web @help
Feature: Help page redirect

  # Note: assert via HTTP redirect (Location header), not browser navigation.
  # Browser navigation triggered Chrome OOM crashes when loading the heavy
  # external catrobat.org/docs/ page in CI.

  Scenario: Navigating to "/app/help" should redirect to the wiki
    Given I do not follow redirects
    When I set request language to "English"
    And I GET "/app/help"
    Then the response status code should be "302"
    And the response Location header should be "https://catrobat.org/docs/"

  Scenario: Navigating to "/app/help" should redirect to a german page
    Given I do not follow redirects
    When I set request language to "Deutsch"
    And I GET "/app/help"
    Then the response status code should be "302"
    And the response Location header should be "https://catrobat.org/de/dokumentation/"
