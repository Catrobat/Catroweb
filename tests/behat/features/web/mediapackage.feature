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
      | id | name    | package |
      | 1  | Animals | Looks   |
      | 2  | Fantasy | Sounds  |
      | 3  | Bla     | Looks   |

    And there are mediapackage files:
      | id | name       | category | extension | active | file   | flavor                   | author        |
      | 1  | Dog (😊🐶)   | Animals  | png       | 1      | 1.png  | pocketcode               | Bob Schmidt   |
      | 2  | Bubble     | Fantasy  | mpga      | 1      | 2.mpga | pocketcode               |               |
      | 3  | SexyGrexy  | Bla      | png       | 0      | 3.png  |                          | Micheal John  |
      | 4  | SexyFlavor | Animals  | png       | 1      | 4.png  | pocketflavor             |               |
      | 5  | SexyNULL   | Animals  | png       | 1      | 5.png  |                          |               |
      | 6  | SexyWolfi  | Animals  | png       | 1      | 6.png  | pocketflavor, pocketcode | Jenifer Shawn |

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
