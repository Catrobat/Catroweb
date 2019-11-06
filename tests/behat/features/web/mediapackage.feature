@homepage
Feature:
  In order to speed up the creation of a pocketcode program
  As UX/Design team
  We want to offer the user a library of assets to work with


  Background:
    Given there are mediapackages:
      | id | name   | name_url |
      | 1  | Looks  | looks    |
      | 2  | Sounds | sounds   |
    And there are mediapackage categories:
      | id | name         | package |
      | 1  | Animals      | Looks   |
      | 2  | Fantasy      | Sounds  |
      | 3  | Bla          | Looks   |
      | 4  | ThemeSpecial | Looks   |

    And there are mediapackage files:
      | id | name       | category     | extension | active | file   | flavor     | author        |
      | 1  | Dog (😊🐶) | Animals      | png       | 1      | 1.png  | pocketcode | Bob Schmidt   |
      | 2  | Bubble     | Fantasy      | mpga      | 1      | 2.mpga | pocketcode |               |
      | 3  | SexyGrexy  | Bla          | png       | 0      | 3.png  | luna       | Micheal John  |
      | 4  | SexyFlavor | Animals      | png       | 1      | 4.png  | luna       |               |
      | 5  | SexyNULL   | Animals      | png       | 1      | 5.png  |            |               |
      | 6  | SexyWolfi  | Animals      | png       | 1      | 6.png  | pocketcode | Jenifer Shawn |
      | 7  | MyLuna     | ThemeSpecial | png       | 1      | 7.png  | luna       |               |

  Scenario: Viewing defined categories in a specific package
    Given I am on "/app/media-library/looks"
    Then I should see "Animals"

  Scenario: Viewing only media files for the pocketcode flavor
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    Then I should see media file with id "1"
    And I should see media file with id "5"
    And I should see media file with id "6"
    But I should not see media file with id "4"
    And I should not see media file with id "7"
    And I should not see a "#category-theme-special" element

  Scenario: When using Luna-Flavor, there should be a theme-special category
    Given I am on "/luna/media-library/looks"
    And I wait for the page to be loaded
    Then I should see a "#category-theme-special" element
    And I should see media file with id 7 in category "Luna & Cat Theme Special"
    And I should see 1 media file in category "Luna & Cat Theme Special"
    And I should see 1 media file in category "Bla"

  Scenario: Selecting and deselecting media files for downloading
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    And I click "#mediafile-1"
    And I click "#mediafile-5"
    Then I should see "Download these files"
    And I click "#mediafile-1"
    And I click "#mediafile-5"
    Then I should not see "Download these files"

  Scenario: Downloading mutiple selected files
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    And I click "#mediafile-1"
    And I click "#mediafile-5"
    And I click "#start-downloads"
    Then I should receive a file named "Dog (-).png"
    Then I should receive a file named "SexyNULL.png"

  Scenario: When viewing a media package category the project navigation in the nav sidebar should be hidden
    Given I am on "/app/media-library/looks"
    And I wait for the page to be loaded
    But I should not see a "#project-navigation" element
