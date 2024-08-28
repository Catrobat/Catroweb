@web @help
Feature: Help page redirect

  Scenario: Navigating to "/app/help" should redirect to the wiki
    When I switch the language to "English"
    And I go to "/app/help"
    And I wait for the page to be loaded
    Then I should be on "https://catrobat.org/docs/"

  Scenario: Navigating to "/app/help" should redirect to a german page
    When I switch the language to "Deutsch"
    And I go to "/app/help"
    And I wait for the page to be loaded
    Then I should be on "https://catrobat.org/de/dokumentation/"