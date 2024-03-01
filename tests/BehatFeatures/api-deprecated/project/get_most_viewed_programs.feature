@api
Feature: Get the most downloaded programs

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 90    | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"

  Scenario: show most viewed programs
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get the json object:
      """
      {
          "CatrobatProjects":[{
                                "ProjectId": "REGEX_STRING_WILDCARD",
                                "ProjectName":"program 2",
                                "ProjectNameShort":"program 2",
                                "Author":"Catrobat",
                                "Description":"",
                                "Version":"0.8.5",
                                "Views": 90,
                                "Downloads": 333,
                                "Private":false,
                                "Uploaded": "REGEX_INT_WILDCARD",
                                "UploadedString":"1 year ago",
                                "ScreenshotBig":"images/default/screenshot.png",
                                "ScreenshotSmall":"images/default/thumbnail.png",
                                "ProjectUrl":"app/project/REGEX_STRING_WILDCARD",
                                "DownloadUrl":"api/project/REGEX_STRING_WILDCARD/catrobat",
                                "FileSize":0
                            }],
          "completeTerm":"",
          "preHeaderMessages":"",
          "CatrobatInformation": {
                                   "BaseUrl":"http://localhost\/",
                                   "TotalProjects":3,
                                   "ProjectsExtension":".catrobat"
                                  }
      }
      """

  Scenario: show most downloaded programs with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | program 2 |
      | program 3 |

  Scenario: show most downloaded programs with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | program 3 |
      | program 1 |

  Scenario: show only visible programs
    Given project "program 3" is not visible
    When I GET "/app/api/projects/mostViewed.json" with these parameters
    Then I should get projects in the following order:
      | Name      |
      | program 2 |
      | program 1 |
            
