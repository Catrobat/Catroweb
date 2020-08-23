@web @help
Feature: Pocketcode help page
  In order to access and browse the help page
  As a visitor
  I want to be able to see the help page

  Background:
    Given I am on "/app/help"
    And I wait for the page to be loaded

  Scenario: Viewing the help overview at help page
    And I should see "Step by step"
    And I should see "Starters"
    And I should see "Education platform"
    And I should see "Tutorials"
    And I should see "Discuss"
    And I should see "Google Play"
    And I should see "IOS"

  Scenario: Viewing the help overview at help page for luna flavor i should see discord instead of ios
    Given I am on "/luna/help"
    And I wait for the page to be loaded
    And I should see "Step by step"
    And I should see "Starters"
    And I should see "Education platform"
    And I should see "Tutorials"
    And I should see "Discuss"
    And I should see "Google Play"
    And I should see "Discord"

  Scenario Outline: Clicking on tutorials image at help page and test navigation
    Given I am on "/app/tutorialcards"
    And I wait for the page to be loaded
    And I should see "<title>" in the "#card-<id>" element
    When I click "#card-<id>"
    And I wait for AJAX to finish
    Then I should see "<title>"

    Examples:
      | id | title               |
      | 1  | Change Size         |
      | 2  | Change Look         |
      | 3  | Animation           |
      | 4  | Glide               |
      | 5  | Play a Sound        |
      | 6  | Speak               |
      | 7  | Sensor              |
      | 8  | Compass             |
      | 9  | Broadcast           |
      | 10 | Show variable       |
      | 11 | Collision detection |
      | 12 | Face detection      |

  Scenario: Clicking on starters image at help page and test navigation
    Given there are users:
      | name     | password | token      | email               | id |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org | 1  |
    And there are starter programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | project 3 |             | Catrobat | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    When I click "#starters"
    And I wait for AJAX to finish
    Then I should see "STARTER PROJECTS"
    And I should see "Try out these starter projects. Look inside to make changes and add your ideas."
    And I should see "Games"
    And I should see "project 1"
    And I should see "project 2"
    And I should see "project 3"
    And I should see an ".anchor" element
    When I click ".anchor"
    And I wait for AJAX to finish
    Then I am on "/app/starterProjects"

  Scenario: /hourOfCode should redirect to help page
    When I go to "/app/hourOfCode"
    And I wait for the page to be loaded
    Then I should see "TUTORIALS"
