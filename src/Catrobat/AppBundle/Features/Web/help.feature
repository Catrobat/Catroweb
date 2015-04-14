@help
Feature: Pocketcode help page
  In order to access and browse the help page
  As a visitor
  I want to be able to see the help page

  Background:
    Given I am on "/help"

  Scenario: Viewing the help overview at help page
    When I should see a big help image "Hour of Code"
    Then I should see a big help image "Step By Step"
    And I should see a help image "Tutorials"
    And I should see a help image "Starters"
    And I should see a big help image "Discussion"

  @Mobile
  Scenario: Viewing the help overview at help page
    When I should see a small help image "Hour of Code"
    Then I should see a small help image "Step By Step"
    And I should see a help image "Tutorials"
    And I should see a help image "Starters"
    And I should see a small help image "Discussion"

  Scenario: Clicking on hour-of-page image at help page and test navigation
    When I click "#hour-of-code-desktop"
    Then  I should see "SKYDIVING STEVE"
    And I should see "#0"




