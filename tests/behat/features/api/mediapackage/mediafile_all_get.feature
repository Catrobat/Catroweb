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
      | 3  | arduino    |

    And there are media package files:
      | id | name      | category | extension | active | file   | flavors             | author         |
      | 1  | Dog 1     | Animals  | png       | 1      | 1.png  | pocketcode          | Bob Schmidt    |
      | 2  | Dog 2     | Fantasy  | mpga      | 1      | 2.mpga | pocketcode          |                |
      | 3  | Spaceship | Space    | png       | 0      | 3.png  | pocketcode, arduino | Micheal John   |
      | 4  | Cat       | Animals  | png       | 1      | 4.png  | luna, arduino       |                |
      | 5  | Ape       | Animals  | png       | 1      | 5.png  | pocketcode          |                |
      | 6  | Metroid   | Space    | png       | 1      | 6.png  | pocketcode          | Jennifer Shawn |


  Scenario: Requests with wrong HTTP_ACCEPT value should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "text/html"
    And I request "GET" "/api/media/files"
    Then the response status code should be "406"

  Scenario: Requests with invalid limit parameter should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "limit" with value "a"
    And I request "GET" "/api/media/files"
    Then the response status code should be "400"

  Scenario: Requests with invalid offset parameter should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "offset" with value "a"
    And I request "GET" "/api/media/files"
    Then the response status code should be "400"

  Scenario: Getting all files with limit "6" should return all media files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "limit" with value "6"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 1,
        "name": "Dog 1",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "Bob Schmidt",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/1"
      },
      {
        "id": 2,
        "name": "Dog 2",
        "flavor": "pocketcode",
        "package": "Sounds",
        "category": "Fantasy",
        "author": "",
        "extension": "mpga",
        "download_url": "http:\/\/localhost\/app\/download-media\/2"
      },
      {
        "id": 3,
        "name": "Spaceship",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/3"
      },
      {
        "id": 4,
        "name": "Cat",
        "flavor": "luna",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4"
      },
      {
        "id": 5,
        "name": "Ape",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/5"
      },
      {
        "id": 6,
        "name": "Metroid",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Jennifer Shawn",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/6"
      }
    ]
    """

  Scenario: Getting files with limit "5" should return first 5 media files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "limit" with value "5"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 1,
        "name": "Dog 1",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "Bob Schmidt",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/1"
      },
      {
        "id": 2,
        "name": "Dog 2",
        "flavor": "pocketcode",
        "package": "Sounds",
        "category": "Fantasy",
        "author": "",
        "extension": "mpga",
        "download_url": "http:\/\/localhost\/app\/download-media\/2"
      },
      {
        "id": 3,
        "name": "Spaceship",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/3"
      },
      {
        "id": 4,
        "name": "Cat",
        "flavor": "luna",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4"
      },
      {
        "id": 5,
        "name": "Ape",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/5"
      }
    ]
    """

  Scenario: Getting files with flavor "luna" should return 1 media file
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "flavor" with value "luna"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 4,
        "name": "Cat",
        "flavor": "luna",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4"
      }
    ]
    """

  Scenario: Getting files with flavor "arduino" should return 2 media file
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "flavor" with value "arduino"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 3,
        "name": "Spaceship",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Micheal John",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/3"
      },
        {
        "id": 4,
        "name": "Cat",
        "flavor": "luna",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4"
      }
    ]
    """

  Scenario: Getting all files with offset "3" should ignore 3 media files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "offset" with value "3"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 4,
        "name": "Cat",
        "flavor": "luna",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4"
      },
        {
        "id": 5,
        "name": "Ape",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/5"
      },
        {
        "id": 6,
        "name": "Metroid",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Jennifer Shawn",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/6"
      }
    ]
    """

  Scenario: Getting files with flavor "pocketcode" and offset 3 should return 2 media files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "flavor" with value "pocketcode"
    And I have a parameter "offset" with value "3"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
     {
        "id": 5,
        "name": "Ape",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/5"
      },
        {
        "id": 6,
        "name": "Metroid",
        "flavor": "pocketcode",
        "package": "Looks",
        "category": "Space",
        "author": "Jennifer Shawn",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/6"
      }
    ]
    """
