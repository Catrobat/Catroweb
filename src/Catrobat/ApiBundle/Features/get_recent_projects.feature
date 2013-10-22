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

  @TODO
  Scenario: show recent projects
    Given I have the limit "1"
    And I have the offset "0"
    When I call "/api/projects/recent.json" with the given data
    Then I should see:
      """
      {
          "completeTerm":"",
          "CatrobatInformation": {
                                   "BaseUrl":"https:\/\/localhost\/",
                                   "TotalProjects":3,
                                   "ProjectsExtension":".catrobat"
                                  },
          "CatrobatProjects":[{
                                "ProjectId":"1",
                                "ProjectName":"project 1",
                                "ProjectNameShort":"project 1",
                                "ScreenshotBig":"resources\/thumbnails\/1_large.png",
                                                      "ScreenshotSmall":"resources\/thumbnails\/1_small.png",
                                                      "Author":"Catrobat",
                                                      "Description":"",
                                                      "Uploaded":<time>,
                                                      "UploadedString":<timestring>,
                                                      "Version":"0.8.5",
                                                      "Views":"12",
                                                      "Downloads":"3",
                                                      "ProjectUrl":"details\/1",
                                                      "DownloadUrl":"download\/1.catrobat"
                                                    }],
          "preHeaderMessages":""
      }
      """
