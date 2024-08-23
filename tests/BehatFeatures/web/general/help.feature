@web @help
Feature: Help page redirect

  Scenario: Navigating to "/app/help" should redirect to the wiki
    When the selected language should be "en"
    And I go to "/app/help"
    And I wait for the page to be loaded
    Then I should be on "https://catrobat.org/docs/"

  Scenario: Navigating to "/app/help" should redirect to a german page
    When the selected language should be "de"
    And I go to "/app/help"
    And I wait for the page to be loaded
    Then I should be on "https://catrobat.org/de/dokumentation/"