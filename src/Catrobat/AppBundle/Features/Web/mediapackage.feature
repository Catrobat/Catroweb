@homepage
Feature:
  In order to speed up the creation of a pocketcode program
  As UX/Design team
  We want to offer the user a library of assets to work with


  Background:
    Given there are mediapackages:
      | id | name    | name_url |
      | 1  | Looks   | looks    |
      | 2  | Sounds  | sounds   |
    And there are mediapackage categories:
      | id | name    | package |
      | 1  | Animals | Looks   |
      | 2  | Fantasy | Sounds  |
      | 3  | Bla     | Looks   |

    And there are mediapackage files:
      | id | name      | category | extension | active | file   |
      | 1  | Dog       | Animals  | png       | 1      | 1.png  |
      | 2  | Bubble    | Fantasy  | mpga      | 1      | 2.mpga |
      | 3  | SexyGrexy | Bla      | png       | 0      | 3.png  |

    Scenario: Viewing defined categories in a specific package
      Given I am on "/pocketcode/media-library/looks"
      Then I should see "Animals"

    Scenario: Download a media file
      When I download "/pocketcode/download-media/1"
      Then I should receive a "png" file
      And the response code should be "200"

    Scenario: The app needs the filename
      When I am on "/pocketcode/media-library/looks"
      Then the link to "Dog" should be "/pocketcode/download-media/1?fname=Dog"