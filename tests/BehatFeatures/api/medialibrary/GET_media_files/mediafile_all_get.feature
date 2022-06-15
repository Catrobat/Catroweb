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
        "name": "Dog 1"
      },
      {
        "id": 2,
        "name": "Dog 2"
      },
      {
        "id": 3,
        "name": "Spaceship"
      },
      {
        "id": 4,
        "name": "Cat"
      },
      {
        "id": 5,
        "name": "Ape"
      },
      {
        "id": 6,
        "name": "Metroid"
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
        "name": "Dog 1"
      },
      {
        "id": 2,
        "name": "Dog 2"
      },
      {
        "id": 3,
        "name": "Spaceship"
      },
      {
        "id": 4,
        "name": "Cat"
      },
      {
        "id": 5,
        "name": "Ape"
      }
    ]
    """

  Scenario: Getting files with flavor "luna" should return 1 media file with specified attributes
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "attributes" with value "id,name,flavors,packages,category,author,extension,download_url,file_type"
    And I have a parameter "flavor" with value "luna"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 4,
        "name": "Cat",
        "flavors": ["luna", "arduino"],
        "packages": ["Looks"],
        "category": "Animals",
        "author": "",
        "extension": "png",
        "download_url": "http:\/\/localhost\/app\/download-media\/4",
        "file_type": "image"
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
        "name": "Spaceship"
      },
        {
        "id": 4,
        "name": "Cat"
      }
    ]
    """

  Scenario: Getting all files with offset "3" should ignore 3 media files and use specified attributes
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "offset" with value "3"
    And I have a parameter "attributes" with value "id,name,flavors,packages"
    And I request "GET" "/api/media/files"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
      {
        "id": 4,
        "name": "Cat",
        "flavors": ["luna", "arduino"],
        "packages": ["Looks"]
      },
        {
        "id": 5,
        "name": "Ape",
        "flavors": ["pocketcode"],
        "packages": ["Looks"]
      },
        {
        "id": 6,
        "name": "Metroid",
        "flavors": ["pocketcode"],
        "packages": ["Looks"]
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
        "name": "Ape"
      },
        {
        "id": 6,
        "name": "Metroid"
      }
    ]
    """
