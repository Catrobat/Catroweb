@api
Feature: List programs with and without debug build type

  Background:
    Given there are users:
      | name           | password | token      | id |
      | GeneratedUser1 | vwxyz    | aaaaaaaaaa | 1  |
    And there are extensions:
      | id | internal_title |
      | 1  | drone          |
      | 2  | mindstorms     |
      | 3  | phiro          |
    And there are projects:
      | id | name          | description | downloads | views | upload time      | version | debug | extensions       | owned by       |
      | 1  | program 1     | p1          | 3         | 12    | 01.01.2013 12:00 | 0.9.10  | false | mindstorms,drone | GeneratedUser1 |
      | 2  | program 2     |             | 333       | 9     | 22.04.2014 13:00 | 0.9.10  | false |                  | GeneratedUser1 |
      | 3  | debug program | new one     | 450       | 80    | 01.04.2019 09:00 | 1.0.12  | true  | mindstorms,phiro | GeneratedUser1 |
      | 4  | program 4     |             | 133       | 33    | 01.01.2012 13:00 | 0.9.10  | false | mindstorms       | GeneratedUser1 |
    And the current time is "01.08.2019 00:00"
    And I store the following json object as "debug_program":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": "REGEX_STRING_WILDCARD",
            "ProjectName": "debug program",
            "ProjectNameShort": "debug program",
            "Author": "GeneratedUser1",
            "Description": "new one",
            "Version": "1.0.12",
            "Views": 80,
            "Downloads": 450,
            "Private": false,
            "Uploaded": "REGEX_INT_WILDCARD",
            "UploadedString": "4 months ago",
            "ScreenshotBig": "images/default/screenshot.png",
            "ScreenshotSmall": "images/default/thumbnail.png",
            "ProjectUrl": "app/project/REGEX_STRING_WILDCARD",
            "DownloadUrl": "api/project/REGEX_STRING_WILDCARD/catrobat",
            "FileSize": 0
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
    And I store the following json object as "debug_program_id":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": "REGEX_STRING_WILDCARD",
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
            "ProjectId": "REGEX_STRING_WILDCARD",
            "ProjectName": "program 2",
            "ProjectNameShort": "program 2",
            "Author": "GeneratedUser1",
            "Description": "",
            "Version": "0.9.10",
            "Views": 9,
            "Downloads": 333,
            "Private": false,
            "Uploaded": "REGEX_INT_WILDCARD",
            "UploadedString": "more than one year ago",
            "ScreenshotBig": "images/default/screenshot.png",
            "ScreenshotSmall": "images/default/thumbnail.png",
            "ProjectUrl": "app/project/REGEX_STRING_WILDCARD",
            "DownloadUrl": "api/project/REGEX_STRING_WILDCARD/catrobat",
            "FileSize": 0
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
    And I store the following json object as "program_2_id":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": "REGEX_STRING_WILDCARD",
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
            "ProjectId": "REGEX_STRING_WILDCARD",
            "ProjectName": "program 4",
            "ProjectNameShort": "program 4",
            "Author": "GeneratedUser1",
            "Description": "",
            "Version": "0.9.10",
            "Views": 33,
            "Downloads": 133,
            "Private": false,
            "Uploaded": "REGEX_INT_WILDCARD",
            "UploadedString": "more than one year ago",
            "ScreenshotBig": "images/default/screenshot.png",
            "ScreenshotSmall": "images/default/thumbnail.png",
            "ProjectUrl": "app/project/REGEX_STRING_WILDCARD",
            "DownloadUrl": "api/project/REGEX_STRING_WILDCARD/catrobat",
            "FileSize": 0
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
    And I store the following json object as "program_4_id":
      """
      {
        "CatrobatProjects": [
          {
            "ProjectId": "REGEX_STRING_WILDCARD",
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

  Scenario Outline: Show most downloaded/viewed/recent program with debug and release app
    Given I request from a <build type> build of the Catroid app
    And I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/<end point>.json" with these parameters
    Then I should get the stored json object "<json name>"

    Examples:
      | end point      | build type | json name     |
      | mostDownloaded | debug      | debug_program |
      | mostDownloaded | release    | program_2     |
      | mostViewed     | debug      | debug_program |
      | mostViewed     | release    | program_4     |
      | recent         | debug      | debug_program |
      | recent         | release    | program_2     |

  Scenario Outline: Show user projects with debug and release app
    Given I request from a <build type> build of the Catroid app
    And I have a parameter "user_id" with value "1"
    When I GET "/app/api/projects/userProjects.json" with these parameters
    Then I should get a total of <total> projects
    And I should get the projects "<programs>"

    Examples:
      | build type | total | programs                                    |
      | debug      | 4     | debug program,program 2,program 1,program 4 |
      | release    | 3     | program 2,program 1,program 4               |

  Scenario Outline: Search for debug program in debug and release app
    Given I request from a <build type> build of the Catroid app
    When searching for "debug"
    Then I should get a total of <total> projects
    And I should get the projects "<programs>"

    Examples:
      | build type | total | programs      |
      | debug      | 1     | debug program |
      | release    | 0     |               |

  Scenario Outline: Search for programs with specific extension in debug and release app
    Given I request from a <build type> build of the Catroid app
    And I have a parameter "q" with value "<q>"
    And I have a parameter "limit" with value "5"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/search/<end point>.json" with these parameters
    Then I should get a total of <total> projects
    And I should get the projects "<programs>"

    Examples:
      | end point         | build type | q          | programs                          | total |
      | extensionProjects | debug      | mindstorms | debug program,program 1,program 4 | 3     |
      | extensionProjects | release    | mindstorms | program 1,program 4               | 2     |
