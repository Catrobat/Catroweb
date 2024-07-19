@api @search
Feature: Search programs

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser  | vwxyz    | aaaaaaaaaa | 3  |
    And there are projects:
      | id | name            | description | owned by | downloads | views | upload time      | version |
      | 1  | Galaxy War      | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Minions         |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | Fisch           |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Ponny           | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | MarkoTheBest    |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Whack the Marko | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
      | 7  | Superponny      | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
      | 8  | Universe        |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | Webteam         |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | Fritz the Cat   |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"
    And the server name is "pocketcode.org"
    And I wait 500 milliseconds

  Scenario: Search for a program

    Given the HTTP Request:
      | Method | GET                           |
      | Url    | /app/api/projects/search.json |
    And the GET parameters:
      | Name   | Value  |
      | q      | Galaxy |
      | limit  | 1      |
      | offset | 0      |
    When the Request is invoked
    Then I should get the json object:
      """
      {
        "CatrobatProjects": [{
          "ProjectId": "REGEX_STRING_WILDCARD",
          "ProjectName": "Galaxy War",
          "ProjectNameShort": "Galaxy War",
          "Author": "User1",
          "Description": "p1",
          "Version": "0.8.5",
          "Views": 12,
          "Downloads": 3,
          "Private": false,
          "Uploaded": "REGEX_INT_WILDCARD",
          "UploadedString": "more than one year ago",
          "ScreenshotBig": "images\/default\/screenshot.png",
          "ScreenshotSmall": "images\/default\/thumbnail.png",
          "ProjectUrl": "app\/project\/REGEX_STRING_WILDCARD",
          "DownloadUrl": "api\/project\/REGEX_STRING_WILDCARD\/catrobat",
          "FileSize": 0
        }],
        "completeTerm": "",
        "preHeaderMessages": "",
        "CatrobatInformation": {
          "BaseUrl": "http:\/\/pocketcode.org\/",
          "TotalProjects": 1,
          "ProjectsExtension": ".catrobat"
        }
      }
      """

  Scenario: No programs are found

    When searching for "NOTHINGTOBEFIOUND"
    Then I should get the json object:
      """
      {
        "CatrobatProjects":[],
        "completeTerm":"",
        "preHeaderMessages":"",
        "CatrobatInformation": {
          "BaseUrl":"http://pocketcode.org/",
          "TotalProjects":0,
          "ProjectsExtension":".catrobat"
        }
      }
      """