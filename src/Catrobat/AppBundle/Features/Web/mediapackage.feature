@homepage
Feature: As a visitor I want to see a program page

  Background:
    Given there are mediapackages:
      | id | name    | name_url |
      | 1  | Looks   | looks    |
      | 2  | Sounds  | sounds   |
    And there are mediapackage categories:
      | id | name    | package |
      | 1  | Animals | Looks   |
      | 2  | Fantasy | Sounds  |
      | 3  | Bla     | Looks  |

    And there are mediapackage files:
      | id | name      | category | extension | active | file   |
      | 1  | Dog       | Animals  | png       | 1      | 1.png  |
      | 2  | Bubble    | Fantasy  | mpga      | 1      | 2.mpga |
      | 3  | SexyGrexy | Bla      | png       | 0      | 3.png  |

    Scenario: Viewing program page
      Given I am on "/pocketcode/pocket-library/looks"
      Then I should see "Animals"
      When I download "/pocketcode/download-media/1"
      Then I should receive a "png" file
      And the response code should be "200"