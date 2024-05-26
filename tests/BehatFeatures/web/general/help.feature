@web @help
Feature: Help page redirect

  @disabled # flaky, we can't control the availability of the wiki
  Scenario: Navigating to "/app/help" should redirect to the wiki
    When I go to "/app/help"
    And I wait for the page to be loaded
    Then I should be on "https://wiki.catrobat.org/bin/view/Documentation/"

  @disabled
  Scenario: Navigating to "/mindstorms/help" should redirect to the mindstorms wiki
    When I go to "/mindstorms/help"
    And I wait for the page to be loaded
    Then I should be on "https://catrob.at/MindstormsFlavorDocumentation"