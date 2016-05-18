@doc
Feature: Show program details

  Background: 
    Given there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version | FileSize |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   | 1024     |
      | 2  | program 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   | 2621440  |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   | 1337     |
    And the current time is "01.08.2014 13:00"
    And the server name is "pocketcode.org"
    
  
  Scenario: Show program details with a given id
  
    Given the HTTP Request:
          | Method | GET                                       |
          | Url    | /pocketcode/api/projects/getInfoById.json |
      And the GET parameters:
          | Name | Value |
          | id   | 2     |
     When the Request is invoked
     Then the returned json object will be:
      """
      {
          "completeTerm":"",
          "CatrobatInformation": {
                                   "BaseUrl":"https://pocketcode.org/",
                                   "TotalProjects":1,
                                   "ProjectsExtension":".catrobat"
                                  },
          "CatrobatProjects":[{
                                "ProjectId": 2,
                                "ProjectName":"program 2",
                                "ProjectNameShort":"program 2",
                                "ScreenshotBig":"images/default/screenshot.png",
                                "ScreenshotSmall":"images/default/thumbnail.png",
                                "Author":"Catrobat",
                                "Description":"",
                                "Uploaded": 1398171600,
                                "UploadedString":"3 months ago",
                                "Version":"0.8.5",
                                "Views":"9",
                                "Downloads":"333",
                                "ProjectUrl":"pocketcode/program/2",
                                "DownloadUrl":"pocketcode/download/2.catrobat",
                                "FileSize":2.5
                            }],
          "preHeaderMessages":""
      }
      """
      
  Scenario: Error if no program is found
  
    Given the HTTP Request:
          | Method | GET                                       |
          | Url    | /pocketcode/api/projects/getInfoById.json |
      And the GET parameters:
          | Name | Value |
          | id   | 9     |
     When the Request is invoked
     Then the returned json object will be:
          """
          {
            "Error": "Project not found (uploaded)",
            "preHeaderMessages": ""
          }
          """
          