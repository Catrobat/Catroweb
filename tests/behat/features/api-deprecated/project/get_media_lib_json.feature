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
    Then I should get the json object:
    """
    [
      {
        "id": 1,
        "name": "Dog",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "Bob Schmidt",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/1"
      },
      {
        "id": 4,
        "name": "Cat",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/4"
      },
      {
        "id": 5,
        "name": "Ape",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/5"
      },
      {
        "id": 3,
        "name": "Spaceship",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/3"
      },
      {
        "id": 6,
        "name": "Metroid",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Jennifer Shawn",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/6"
      }
    ]
    """

  Scenario: get all files from a media lib package by nameUrl
    When I GET from the api "/app/api/media/packageByNameUrl/looks/json"
    Then I should get the json object:
    """
    [
      {
        "id": 1,
        "name": "Dog",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "Bob Schmidt",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/1"
      },
      {
        "id": 4,
        "name": "Cat",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/4"
      },
      {
        "id": 5,
        "name": "Ape",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/5"
      },
      {
        "id": 3,
        "name": "Spaceship",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/3"
      },
      {
        "id": 6,
        "name": "Metroid",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Jennifer Shawn",
        "extension": "png",
        "url": null,
        "download_url": "\/app\/download-media\/6"
      }
    ]
    """