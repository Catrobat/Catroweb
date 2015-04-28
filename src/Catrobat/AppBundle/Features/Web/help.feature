@help
Feature: Pocketcode help page
  In order to access and browse the help page
  As a visitor
  I want to be able to see the help page

  Background:
    Given I am on "/pocketcode/help"

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

  Scenario: Clicking on hour-of-page-desktop image at help page and test navigation
    When I click "#hour-of-code-desktop"
    Then  I should see "SKYDIVING STEVE"
    And I should see "#0"
    And I should not see an ".arrow.left" element
    And I should see an ".arrow.right" element
    And I should see "0" in the ".current" element
    When I click ".arrow.right"
    Then I should see "#1"
    And I should see "Install \"Pocket Code\""
    And I should see an ".arrow.left" element
    And I should see an ".arrow.right" element
    And I should see "1" in the ".current" element
    When I go to "/pocketcode/hour-of-code/21"
    Then I should see "#21"
    And I should see "Check your scripts!"
    And I should see an ".arrow.left" element
    And I should not see an ".arrow.right" element
    And I should see "21" in the ".current" element

  @Mobile
  Scenario: Clicking on hour-of-page-mobile image at help page and test navigation
    When I click "#hour-of-code-mobile"
    Then  I should see "SKYDIVING STEVE"

  Scenario: Clicking on step-by-step-desktop image at help page and test navigation
    When I click "#step-by-step-desktop"
    Then  I should see "1. Make a new program"
    And I should see "1" in the ".bubbles" element
    And I should not see an ".arrow.left" element
    And I should see an ".arrow.right" element
    And I should see "1" in the ".current" element
    When I click ".arrow.right"
    Then I should see "2. Create a new object"
    And I should see an ".arrow.left" element
    And I should see an ".arrow.right" element
    And I should see "2" in the ".current" element
    When I go to "/pocketcode/step-by-step/11"
    Then I should see "11. Main Menu"
    And I should see an ".arrow.left" element
    And I should not see an ".arrow.right" element
    And I should see "11" in the ".current" element

  @Mobile
  Scenario: Clicking on step-by-step-mobile image at help page and test navigation
    When I click "#step-by-step-mobile"
    Then  I should see "1. Make a new program"

  Scenario: Clicking on tutorials image at help page and test navigation
    When I click "#tutorials"
    Then  I should see "TUTORIALS"
    Then  I should see "This tutorials show you how to use effective tricks in POCKET CODE."

  Scenario Outline: Clicking on tutorials image at help page and test navigation
    Given I am on "/pocketcode/tutorialcards"
    And I should see "<title>" in the "#title-<id>" element
    When I click "#title-<id>"
    Then I should see "<title>"

  Examples:
    | id | title            |
    | 1  | Change Size      |
    | 2  | Change Look      |
    | 3  | Moving Animation |
    | 4  | Glide            |
    | 5  | Play Sound       |
    | 6  | Speak Something  |
    | 7  | GSensor          |
    | 8  | Compass          |
    | 9  | Broadcast        |

  Scenario: Clicking on starters image at help page and test navigation
    Given there are users:
      | name     | password | token      |
      | Catrobat | 123456   | cccccccccc |
    And there are starter programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    When I click "#starters"
    Then I should see "STARTER PROGRAMS"
    And I should see "Try out these starter programs. Look inside to make changes and add your ideas."
    And I should see "Games"
    And I should see "program 1"
    And I should see "program 2"
    And I should see "program 3"
    And I should see an ".anchor" element
    When I click ".anchor"
    Then I am on "/pocketcode/starterPrograms"

  Scenario: Clicking on discuss-desktop image at help page and test navigation
    When I click "#discuss-desktop"
    Then I should see an "#discuss-desktop" element

  @Mobile
  Scenario: Clicking on discuss-mobile image at help page and test navigation
    When I click "#discuss-mobile"
    Then I should see an "#discuss-mobile" element
