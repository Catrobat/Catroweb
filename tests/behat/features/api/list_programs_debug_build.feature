@api
Feature: List programs with and without debug build type

  Background:
    Given there are tags:
      | id | en     | de         |
      | 1  | Games  | Spiele     |
      | 2  | Story  | Geschichte |
      | 3  | Racing | Rennen     |
    And there are extensions:
      | id | name  | prefix |
      | 1  | Drone | DRONE  |
      | 2  | Lego  | LEGO   |
      | 3  | Phiro | PHIRO  |
    And there are programs:
      | id | name          | description | downloads | views | upload time      | version | debug | tags_id | extensions |
      | 1  | program 1     | p1          | 3         | 12    | 01.01.2013 12:00 | 0.9.10  | false | 1,2     | Lego,Drone |
      | 2  | program 2     |             | 333       | 9     | 22.04.2014 13:00 | 0.9.10  | false | 3       |            |
      | 3  | debug program | new one     | 450       | 80    | 01.04.2019 09:00 | 1.0.12  | true  | 1,2,3   | Lego,Phiro |
      | 4  | program 4     |             | 133       | 33    | 01.01.2012 13:00 | 0.9.10  | false | 1       | Lego       |
    And the current time is "01.08.2019 00:00"
    And I store the following json object as "debug_program":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": 3,
            "ProjectName": "debug program",
            "ProjectNameShort": "debug program",
            "Author": "GeneratedUser1",
            "Description": "new one",
            "Version": "1.0.12",
            "Views": "80",
            "Downloads": "450",
            "Private": false,
            "Uploaded": 1554109200,
            "UploadedString": "4 months ago",
            "ScreenshotBig": "images/default/screenshot.png",
            "ScreenshotSmall": "images/default/thumbnail.png",
            "ProjectUrl": "app/program/3",
            "DownloadUrl": "app/download/3.catrobat",
            "FileSize": 0
          }
        ],
        "completeTerm": "",
        "CatrobatInformation": {
          "BaseUrl": "http://localhost/",
          "TotalProjects": 4,
          "ProjectsExtension": ".catrobat"
        },
        "preHeaderMessages": ""
      }
      """
    And I store the following json object as "debug_program_id":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": 3,
            "ProjectName": "debug program"
          }
        ],
        "completeTerm": "",
        "preHeaderMessages": "",
        "CatrobatInformation": {
          "BaseUrl": "http://localhost/",
          "TotalProjects": 4,
          "ProjectsExtension": ".catrobat"
        }
      }
      """
    And I store the following json object as "program_2":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": 2,
            "ProjectName": "program 2",
            "ProjectNameShort": "program 2",
            "Author": "GeneratedUser1",
            "Description": "",
            "Version": "0.9.10",
            "Views": "9",
            "Downloads": "333",
            "Private": false,
            "Uploaded": 1398171600,
            "UploadedString": "more than one year ago",
            "ScreenshotBig": "images/default/screenshot.png",
            "ScreenshotSmall": "images/default/thumbnail.png",
            "ProjectUrl": "app/program/2",
            "DownloadUrl": "app/download/2.catrobat",
            "FileSize": 0
          }
        ],
        "completeTerm": "",
        "CatrobatInformation": {
          "BaseUrl": "http://localhost/",
          "TotalProjects": 3,
          "ProjectsExtension": ".catrobat"
        },
        "preHeaderMessages": ""
      }
      """
    And I store the following json object as "program_2_id":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": 2,
            "ProjectName": "program 2"
          }
        ],
        "completeTerm": "",
        "preHeaderMessages": "",
        "CatrobatInformation": {
          "BaseUrl": "http://localhost/",
          "TotalProjects": 3,
          "ProjectsExtension": ".catrobat"
        }
      }
      """
    And I store the following json object as "program_4":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": 4,
            "ProjectName": "program 4",
            "ProjectNameShort": "program 4",
            "Author": "GeneratedUser1",
            "Description": "",
            "Version": "0.9.10",
            "Views": "33",
            "Downloads": "133",
            "Private": false,
            "Uploaded": 1325422800,
            "UploadedString": "more than one year ago",
            "ScreenshotBig": "images/default/screenshot.png",
            "ScreenshotSmall": "images/default/thumbnail.png",
            "ProjectUrl": "app/program/4",
            "DownloadUrl": "app/download/4.catrobat",
            "FileSize": 0
          }
        ],
        "completeTerm": "",
        "CatrobatInformation": {
          "BaseUrl": "http://localhost/",
          "TotalProjects": 3,
          "ProjectsExtension": ".catrobat"
        },
        "preHeaderMessages": ""
      }
      """
    And I store the following json object as "program_4_id":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": 4,
            "ProjectName": "program 4"
          }
        ],
        "completeTerm": "",
        "preHeaderMessages": "",
        "CatrobatInformation": {
          "BaseUrl": "http://localhost/",
          "TotalProjects": 3,
          "ProjectsExtension": ".catrobat"
        }
      }
      """

  Scenario Outline: Show most downloaded/viewed/recent program (ids) with debug and release app
    Given I use a <build type> build of the Catroid app
    And I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/<end point>.json" with these parameters
    Then I should get the stored json object "<json name>"

    Examples:
      | end point         | build type | json name        |
      | mostDownloaded    | debug      | debug_program    |
      | mostDownloaded    | release    | program_2        |
      | mostDownloadedIDs | debug      | debug_program_id |
      | mostDownloadedIDs | release    | program_2_id     |
      | mostViewed        | debug      | debug_program    |
      | mostViewed        | release    | program_4        |
      | mostViewedIDs     | debug      | debug_program_id |
      | mostViewedIDs     | release    | program_4_id     |
      | recent            | debug      | debug_program    |
      | recent            | release    | program_2        |
      | recentIDs         | debug      | debug_program_id |
      | recentIDs         | release    | program_2_id     |

  Scenario Outline: Show random programs (ids) with debug and release app
    Given I use a <build type> build of the Catroid app
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/<end point>.json" with these parameters
    Then I should get a total of <total> projects
    And I should get the programs "<programs>" in random order

    Examples:
      | end point        | build type | total | programs                                    |
      | randomPrograms   | debug      | 4     | program 1,program 2,debug program,program 4 |
      | randomPrograms   | release    | 3     | program 1,program 2,program 4               |
      | randomProgramIDs | debug      | 4     | program 1,program 2,debug program,program 4 |
      | randomProgramIDs | release    | 3     | program 1,program 2,program 4               |

  Scenario Outline: Show user projects with debug and release app
    Given I use a <build type> build of the Catroid app
    And I have a parameter "user_id" with value "1"
    When I GET "/app/api/projects/userPrograms.json" with these parameters
    Then I should get a total of <total> projects
    And I should get the programs "<programs>"

    Examples:
      | build type | total | programs                                    |
      | debug      | 4     | debug program,program 2,program 1,program 4 |
      | release    | 3     | program 2,program 1,program 4               |

  Scenario Outline: Search for debug program in debug and release app
    Given I use a <build type> build of the Catroid app
    When searching for "debug"
    Then I should get a total of <total> projects
    And I should get the programs "<programs>"

    Examples:
      | build type | total | programs      |
      | debug      | 1     | debug program |
      | release    | 0     |               |

  Scenario Outline: Search for programs with specific tag in debug and release app
    Given I use a <build type> build of the Catroid app
    And I have a parameter "q" with the tag id "1"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search/tagPrograms.json" with these parameters
    Then I should get a total of <total> projects
    And I should get the programs "<programs>"
    Examples:
      | build type | programs                          | total |
      | debug      | debug program,program 1,program 4 | 3     |
      | release    | program 1,program 4               | 2     |

  Scenario Outline: Search for programs with specific extension in debug and release app
    Given I use a <build type> build of the Catroid app
    And I have a parameter "q" with value "<q>"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search/<end point>.json" with these parameters
    Then I should get a total of <total> projects
    And I should get the programs "<programs>"

    Examples:
      | end point         | build type | q    | programs                          | total |
      | extensionPrograms | debug      | Lego | debug program,program 1,program 4 | 3     |
      | extensionPrograms | release    | Lego | program 1,program 4               | 2     |
