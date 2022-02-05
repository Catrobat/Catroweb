@api
Feature: Get data from the media library in json format

  Background:
    Given there are media packages:
      | id | name   | name_url |
      | 1  | Looks  | looks    |
      | 2  | Sounds | sounds   |
    And there are media package categories:
      | id | name    | package |
      | 1  | Animals | Looks   |
      | 2  | Fantasy | Sounds  |
      | 3  | Space   | Looks   |
    And there are flavors:
      | name       |
      | pocketcode |
      | luna       |

    And there are media package files:
      | id | name      | category | extension | active | file   | flavors    | author         |
      | 1  | Dog       | Animals  | png       | 1      | 1.png  | pocketcode | Bob Schmidt    |
      | 2  | Magic     | Fantasy  | mpga      | 1      | 2.mpga | pocketcode |                |
      | 3  | Spaceship | Space    | png       | 0      | 3.png  |            | Micheal John   |
      | 4  | Cat       | Animals  | png       | 1      | 4.png  | pocketcode |                |
      | 5  | Ape       | Animals  | png       | 1      | 5.png  |            |                |
      | 6  | Metroid   | Space    | png       | 1      | 6.png  | pocketcode | Jennifer Shawn |

  Scenario: get all files from a media lib package
    When I GET from the api "/app/api/media/package/Looks/json"
    Then the response code should be "200"

  Scenario: get all files from a media lib package by nameUrl
    When I GET from the api "/app/api/media/packageByNameUrl/looks/json"
    Then the response code should be "200"