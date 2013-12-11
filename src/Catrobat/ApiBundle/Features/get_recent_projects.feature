@api
Feature: Get the most recent projects

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | project 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | project 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | project 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

      
  Scenario: show recent projects
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I POST these parameters to "/api/projects/recent.json"
    Then I should get the json object:
      """
      {
          "completeTerm":"",
          "CatrobatInformation": {
                                   "BaseUrl":"https:\/\/localhost\/",
                                   "TotalProjects":3,
                                   "ProjectsExtension":".catrobat"
                                  },
          "CatrobatProjects":[{
                                "ProjectId": 2,
                                "ProjectName":"project 2",
                                "ProjectNameShort":"project 2",
                                "ScreenshotBig":"resources\/thumbnails\/2_large.png",
                                                      "ScreenshotSmall":"resources\/thumbnails\/2_small.png",
                                                      "Author":"Catrobat",
                                                      "Description":"",
                                                      "Uploaded": 1359723600,
                                                      "UploadedString":"",
                                                      "Version":"0.8.5",
                                                      "Views":"9",
                                                      "Downloads":"33",
                                                      "ProjectUrl":"details\/2",
                                                      "DownloadUrl":"download\/2.catrobat"
                                                    }],
          "preHeaderMessages":""
      }
      """

  Scenario: show recent projects with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    When I POST these parameters to "/api/projects/recent.json"
    Then I should get projects in the following order:
      | Name      |
      | project 2 |
      | project 1 |

  Scenario: show recent projects with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    When I POST these parameters to "/api/projects/recent.json"
    Then I should get projects in the following order:
      | Name      |
      | project 1 |
      | project 3 |

      