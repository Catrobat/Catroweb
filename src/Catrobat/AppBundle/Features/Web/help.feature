@help
Feature: Pocketcode help page
  In order to access and browse the help page
  As a visitor
  I want to be able to see the help page

  Scenario: Viewing the help overview at help page
    When I am on "help"
    Then I should see a big help image "Hour of Code"
    And I should see a big help image "Step By Step"
    And I should see a help image "Tutorials"
    And I should see a help image "Starters"
    And I should see a big help image "Discussion"

  @Mobile
  Scenario: Viewing the help overview at help page
    When I am on "help"
    Then I should see a small help image "Hour of Code"
    And I should see a small help image "Step By Step"
    And I should see a help image "Tutorials"
    And I should see a help image "Starters"
    And I should see a small help image "Discussion"



