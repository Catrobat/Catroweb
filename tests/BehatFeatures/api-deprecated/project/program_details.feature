@api
Feature: Get details for a specific program

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version | file_size |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | 1024      |
      | 2  | program 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   | 2621440   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | 1337      |
    And the current time is "01.08.2014 13:00"

  Scenario: show details of a program with given id
    Given I have a parameter "id" with value "2"
    When I GET "/app/api/projects/getInfoById.json" with these parameters
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
                                "Views":9,
                                "Downloads":333,
                                "Private":false,
                                "Uploaded": "REGEX_INT_WILDCARD",
                                "UploadedString":"3 months ago",
                                "ScreenshotBig":"images/default/screenshot.png",
                                "ScreenshotSmall":"images/default/thumbnail.png",
                                "ProjectUrl":"app/project/REGEX_STRING_WILDCARD",
                                "DownloadUrl":"api/project/REGEX_STRING_WILDCARD/catrobat",
                                "FileSize":2.5

                            }],
          "completeTerm":"",
          "preHeaderMessages":"",
          "CatrobatInformation": {
                                   "BaseUrl":"http://localhost/",
                                   "TotalProjects":1,
                                   "ProjectsExtension":".catrobat"
                                  }
      }
      """

  Scenario: return error if no program matches the given id
    Given I have a parameter "id" with value "9"
    When I GET "/app/api/projects/getInfoById.json" with these parameters
    Then I should get the json object:
      """
        {
            "Error": "Project not found (uploaded)",
            "preHeaderMessages": ""
        }
      """

