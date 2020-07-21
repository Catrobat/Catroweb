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
    And I request "GET" "/api/media/file/5"
    Then the response status code should be "406"

  Scenario: Requests without specified id should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/file/a"
    Then the response status code should be "404"

  Scenario: Requests with non-existing id should result in an error
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/file/100"
    Then the response status code should be "404"

  Scenario: Getting file with id should return one media file with that id
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/file/5"
    Then the response status code should be "200"
    And I should get the json object:
    """
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
    """

  Scenario: Getting file with id should return one media file with that exact id
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/file/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
    {
      "id": 1,
      "name": "Dog 1",
      "flavor": "pocketcode",
      "package": "Looks",
      "category": "Animals",
      "author": "Bob Schmidt",
      "extension": "png",
      "download_url": "http:\/\/localhost\/app\/download-media\/1"
    }
    """
