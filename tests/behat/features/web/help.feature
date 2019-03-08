@help
Feature: Pocketcode help page
  In order to access and browse the help page
  As a visitor
  I want to be able to see the help page

  Background:
    Given I am on "/pocketcode/help"

  Scenario: Viewing the help overview at help page
    Then I wait for a second
    And I should see "Step by step"
    And I should see "Starters"
    And I should see "Education platform"
    And I should see "Game Design"
    And I should see "Tutorials"
    And I should see "Discuss"
    And I should see "Google Play"
    And I should see "IOS"

  Scenario: Viewing the help overview at help page for luna flavor
    Given I am on "/luna/help"
    Then I wait for a second
    And I should see "Step by step"
    And I should see "Starters"
    And I should see "Education platform"
    And I should see "Game Design"
    And I should see "Tutorials"
    And I should see "Discuss"
    And I should see "Google Play"
    And I should see "IOS"

  Scenario Outline: Clicking on the alice game jam image at help page
    When I click "#game-design"
    Then I should see "6" "desktop" tutorial banners
    When I click on the "<reference>" banner
    Then I should see "<title>"

    Examples:
      | reference | title                    |
      | first     | (WELCOME TO) WONDERLAND  |
      | second    | SAVE ALICE!              |
      | third     | THE HATTER - HIT AND RUN |
      | fourth    | THE HATTER - HIT AND RUN |
      | fifth     | WHACK A CHESHIRE CAT     |
      | sixth     | A RABBITS RACE           |


  Scenario Outline: Clicking on tutorials image at help page and test navigation
    Given I am on "/pocketcode/tutorialcards"
    And I should see "<title>" in the "#title-<id>" element
    When I click "#title-<id>"
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
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
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

  Scenario: Game Jam page should be there
    When I go to "/pocketcode/pocket-game-jam"
    Then I should see "HOW TO UPLOAD A POCKET CODE GAME TO THE GAME JOLT SITE?"
    And I should see "1. Registration"
    And I should see "2. Upload"
    And I should see "3. Search for your program"
    And I should see "4. Create Android app"
    And I should see "5. Download app"
    And I should see "6. Register/Login at GameJolt.com"
    And I should see "7. Upload your game on the Game Jolt Site"

  Scenario: /hourOfCode should redirect to help page
    When I go to "/pocketcode/hourOfCode"
    Then I should see "TUTORIALS"
