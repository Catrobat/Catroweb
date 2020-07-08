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
      | 1  | Dog 1     | Animals  | png       | 1      | 1.png  | pocketcode | Bob Schmidt    |
      | 2  | Dog 2     | Fantasy  | mpga      | 1      | 2.mpga | pocketcode |                |
      | 3  | Spaceship | Space    | png       | 0      | 3.png  | pocketcode | Micheal John   |
      | 4  | Cat       | Animals  | png       | 1      | 4.png  | luna       |                |
      | 5  | Ape       | Animals  | png       | 1      | 5.png  | pocketcode |                |
      | 6  | Metroid   | Space    | png       | 1      | 6.png  | pocketcode | Jennifer Shawn |


  Scenario: Requests with wrong HTTP_ACCEPT value should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "text/html"
    And I have a parameter "query" with value "Dog"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "406"

  Scenario: Requests with missing search term parameter should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "400"

  Scenario: Requests with invalid limit parameter should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Dog"
    And I have a parameter "limit" with value "a"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "400"

  Scenario: Requests with invalid offset parameter should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Dog"
    And I have a parameter "offset" with value "a"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "400"

  Scenario: Searching files in the media library for "Dog" should return all files containing "dog" in their name
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Dog"
    And I request "GET" "/api/media/files/search"
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
      }
    ]
    """

  Scenario: Searching files in the media library for "o" and offset set to 1 and limit set to 2 should return 2 files
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "o"
    And I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "200"
    And I should get the json object:
    """
    [
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

  Scenario: Searching files in the media library for "Ape" should return the only file containing "Ape" in the name
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Ape"
    And I request "GET" "/api/media/files/search"
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
      }
    ]
    """

  Scenario: Searching files in the media library for "Elephant" should return an empty result
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Elephant"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "200"
    And I should get the json object:
    """
    []
    """

  Scenario: Searching files in the media library for "Cat" with the "pocketcode" app should return an empty result
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Cat"
    And I have a parameter "flavor" with value "pocketcode"
    And I request "GET" "/api/media/files/search"
    Then the response status code should be "200"
    And I should get the json object:
    """
    []
    """

  Scenario: Searching files in the media library for "Cat" with the "luna" app should return one result
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "query" with value "Cat"
    And I have a parameter "flavor" with value "luna"
    And I request "GET" "/api/media/files/search"
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
