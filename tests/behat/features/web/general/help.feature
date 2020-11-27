@web @help
Feature: Pocketcode help page redirect

  Scenario: Navigating to "/app/help" should redirect to the wiki
    When I go to "/app/help"
    And I wait for the page to be loaded
    Then I should be on "https://wiki.catrobat.org/bin/view/Documentation/"
