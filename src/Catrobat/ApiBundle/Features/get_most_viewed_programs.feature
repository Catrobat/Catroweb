@api
Feature: Get the most downloaded programs

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 90    | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"
      
  Scenario: show most downloaded programs
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/api/projects/mostViewed.json" with these parameters
    Then I should get the json object:
      """
      {
          "completeTerm":"",
          "CatrobatInformation": {
                                   "BaseUrl":"http://localhost\/",
                                   "TotalProjects":3,
                                   "ProjectsExtension":".catrobat"
                                  },
          "CatrobatProjects":[{
                                "ProjectId": 2,
                                "ProjectName":"program 2",
                                "ProjectNameShort":"program 2",
                                "ScreenshotBig":"resources_test/screenshots/screen_2.png",
                                "ScreenshotSmall":"resources_test/thumbnails/screen_2.png",
                                "Author":"Catrobat",
                                "Description":"",
                                "Uploaded": 1359723600,
                                "UploadedString":"1 year ago",
                                "Version":"0.8.5",
                                "Views":"90",
                                "Downloads":"333",
                                "ProjectUrl":"details/2",
                                "DownloadUrl":"download/2.catrobat"
                            }],
          "preHeaderMessages":""
      }
      """

  Scenario: show most downloaded programs with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    When I GET "/api/projects/mostViewed.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 2 |
      | program 3 |

  Scenario: show most downloaded programs with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "1"
    When I GET "/api/projects/mostViewed.json" with these parameters
    Then I should get programs in the following order:
      | Name      |
      | program 3 |
      | program 1 |

