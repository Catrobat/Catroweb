@api @media-package
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
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |

    And there are media package files:
      | id | name      | category | extension | active | file   | flavors    | author         |
      | 1  | Dog       | Animals  | png       | 1      | 1.png  | pocketcode | Bob Schmidt    |
      | 2  | Magic     | Fantasy  | mpga      | 1      | 2.mpga | pocketcode |                |
      | 3  | Spaceship | Space    | png       | 0      | 3.png  | pocketcode | Micheal John   |
      | 4  | Cat       | Animals  | png       | 1      | 4.png  | pocketcode |                |
      | 5  | Ape       | Animals  | png       | 1      | 5.png  | pocketcode |                |
      | 6  | Metroid   | Space    | png       | 1      | 6.png  | pocketcode | Jennifer Shawn |


  Scenario: Requesting files from a non-existing package should result in an error 404
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/package/looksssss"
    Then the response status code should be "404"


  Scenario: Get all files from a media lib package
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/package/looks"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 1,
        "name": "Dog",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Animals",
        "author": "Bob Schmidt",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/1"
      },
        {
        "id": 4,
        "name": "Cat",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4"
      },
      {
        "id": 5,
        "name": "Ape",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/5"
      },
            {
        "id": 3,
        "name": "Spaceship",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/3"
      },
      {
        "id": 6,
        "name": "Metroid",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Space",
        "author": "Jennifer Shawn",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/6"
      }
    ]
    """

  Scenario: Get all files from a media lib package with limit = 1
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/package/looks?limit=1"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 1,
        "name": "Dog",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Animals",
        "author": "Bob Schmidt",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/1"
      }
    ]
    """

  Scenario: Get all files from a media lib package with limit = 1 and offset = 3
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/package/looks?limit=1&offset=3"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
       {
        "id": 3,
        "name": "Spaceship",
        "flavors": ["pocketcode"],
        "packages": ["Looks"],
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/3"
      }
    ]
    """

  Scenario: Get all files from a media lib package with limit = 1 and offset = 3
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/package/looks?offset=5"
    Then the response status code should be "200"
    Then the response should have the media files model structure
    Then I should get the json object:
      """
      []
      """
