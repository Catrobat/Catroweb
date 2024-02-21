@api
Feature: Get the most recent projects

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"

  Scenario: show recent projects
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/recent.json" with these parameters
    Then I should get the json object:
      """
      {
          "CatrobatProjects":[{
                                "ProjectId": "REGEX_STRING_WILDCARD",
                                "ProjectName":"project 2",
                                "ProjectNameShort":"project 2",
                                "Author":"Catrobat",
                                "Description":"",
                                "Version":"0.8.5",
                                "Views":9,
                                "Downloads":33,
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
                                   "BaseUrl":"http://localhost/",
                                   "TotalProjects":3,
                                   "ProjectsExtension":".catrobat"
                                  }
      }
      """

  Scenario: show recent projects with limit and offset
    When I get the most recent projects with limit "2" and offset "0"
    Then I should get projects in the following order:
      | Name      |
      | project 2 |
      | project 1 |

  Scenario: show recent projects with limit and offset
    When I get the most recent projects with limit "3" and offset "1"
    Then I should get projects in the following order:
      | Name      |
      | project 1 |
      | project 3 |

  Scenario: show only visible projects
    Given project "project 1" is not visible
    When I get the most recent projects
    Then I should get projects in the following order:
      | Name      |
      | project 2 |
      | project 3 |

  Scenario: show recent projects after uploading a new project
    Given I am "Catrobat"
    And I have a project with "name" set to "WebTeam"
    When I upload this generated project, API version 1
    And I get the most recent projects
    Then I should get projects in the following order:
      | Name      |
      | WebTeam   |
      | project 2 |
      | project 1 |
      | project 3 |

  Scenario: show recent projects after updating an existing project
    Given I am "Catrobat"
    And I upload the project with "WebTeam" as name, API version 1
    And I upload the project with "WebTeamV2" as name, API version 1
    And I upload the project with "WebTeam" as name again, API version 1
    When I get the most recent projects
    Then I should get projects in the following order:
      | Name      |
      | WebTeam   |
      | WebTeamV2 |
      | project 2 |
      | project 1 |
      | project 3 |
