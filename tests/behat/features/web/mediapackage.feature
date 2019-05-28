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
    Given I am on "/pocketcode/media-library/looks"
    Then I should see "Animals"

  Scenario: Download a media file
    When I download "/pocketcode/download-media/1"
    Then I should receive a "png" file
    And I should receive a file named "Dog (-).png"
    And the response code should be "200"

  Scenario: The app needs the filename, so the media file link must provide the media file's name
    When I am on "/pocketcode/media-library/looks"
    Then the media file "1" must have the download url "/pocketcode/download-media/1"

  Scenario: Viewing only media files for the pocketcode flavor
    Given I am on "/pocketcode/media-library/looks"
    Then I should see media file with id "1"
    And I should see media file with id "5"
    And I should see media file with id "6"
    But I should not see media file with id "4"
    And I should not see media file with id "7"
    And I should not see a "#category-theme-special" element

  Scenario: When using Luna-Flavor, there should be a theme-special category
    Given I am on "/luna/media-library/looks"
    Then I should see a "#category-theme-special" element
    And I should see media file with id 7 in category "Luna & Cat Theme Special"
    And I should see 1 media file in category "Luna & Cat Theme Special"
    And I should see 1 media file in category "Bla"
