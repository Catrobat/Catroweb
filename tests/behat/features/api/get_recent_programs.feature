@api
Feature: Get the most recent programs

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"

  Scenario: show recent programs
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/recent.json" with these parameters
    Then I should get the json object:
      """
      {
          "CatrobatProjects":[{
                                "ProjectId": 2,
                                "ProjectName":"program 2",
                                "ProjectNameShort":"program 2",
                                "Author":"Catrobat",
                                "Description":"",
                                "Version":"0.8.5",
                                "Views":"9",
                                "Downloads":"33",
                                "Private":false,
                                "Uploaded": 1359723600,
                                "UploadedString":"1 year ago",
                                "ScreenshotBig":"images/default/screenshot.png",
                                "ScreenshotSmall":"images/default/thumbnail.png",
                                "ProjectUrl":"app/program/2",
                                "DownloadUrl":"app/download/2.catrobat",
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

  Scenario: show recent program ids
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/recentIDs.json" with these parameters
    Then I should get the json object:
      """
      {
          "CatrobatProjects":[{
                                "ProjectId": 2,
                                "ProjectName":"program 2"
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

  Scenario: show recent programs with limit and offset
    When I get the most recent programs with limit "2" and offset "0"
    Then I should get programs in the following order:
      | Name      |
      | program 2 |
      | program 1 |

  Scenario: show recent programs with limit and offset
    When I get the most recent programs with limit "3" and offset "1"
    Then I should get programs in the following order:
      | Name      |
      | program 1 |
      | program 3 |

  Scenario: show only visible programs
    Given program "program 1" is not visible
    When I get the most recent programs
    Then I should get programs in the following order:
      | Name      |
      | program 2 |
      | program 3 |

  Scenario: show recent programs after uploading a new program
    Given I am "Catrobat"
    And I have a program with "WebTeam" as name
    When I upload this program
    And I get the most recent programs
    Then I should get programs in the following order:
      | Name      |
      | WebTeam   |
      | program 2 |
      | program 1 |
      | program 3 |

  Scenario: show recent programs after updating an existing program
    Given I am "Catrobat"
    And I upload the program with "WebTeam" as name
    And I upload the program with "WebTeamV2" as name
    And I upload the program with "WebTeam" as name again
    When I get the most recent programs
    Then I should get programs in the following order:
      | Name      |
      | WebTeam   |
      | WebTeamV2 |
      | program 2 |
      | program 1 |
      | program 3 |
