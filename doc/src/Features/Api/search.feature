@doc
Feature: Searching programs

  Background: 
    Given there are programs:
          | id | name             | description | owned by | downloads | views | upload time      | version |
          | 1  | Galaxy War       | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
          | 2  | Minions          |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
          | 3  | Fisch            |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
          | 4  | Ponny            | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
          | 5  | MarkoTheBest     |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
          | 6  | Whack the Marko  | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
          | 7  | Superponny       | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
          | 8  | Universe         |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
          | 9  | Webteam          |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
          | 10 | Fritz the Cat    |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |
      And the current time is "01.08.2014 13:00"

  Scenario: Search for a program
  
    Given The HTTP Request:
          | Method | GET                                  |
          | Url    | /pocketcode/api/projects/search.json |
      And The GET parameters:
          | Name   | Value  | 
          | q      | Galaxy |
          | limit  | 1      |
          | offset | 0      |
     When The Request is invoked
     Then The returned json object will be:
          """
          {
            "completeTerm": "",
            "CatrobatInformation": {
              "BaseUrl": "http://localhost/",
              "TotalProjects": 1,
              "ProjectsExtension": ".catrobat"
            },
            "CatrobatProjects":[{
              "ProjectId": 1,
              "ProjectName": "Galaxy War",
              "ProjectNameShort": "Galaxy War",
              "ScreenshotBig": "images/default/screenshot.png",
              "ScreenshotSmall": "images/default/thumbnail.png",
              "Author": "User1",
              "Description": "p1",
              "Uploaded": 1357041600,
              "UploadedString": "more than one year ago",
              "Version": "0.8.5",
              "Views": "12",
              "Downloads": "3",
              "ProjectUrl": "pocketcode/program/1",
              "DownloadUrl": "pocketcode/download/1.catrobat",
              "FileSize": 0
            }],
            "preHeaderMessages":""
          }
          """
          
  Scenario: No programs are found
  
    When Searching for "NOTHINGTOBEFIOUND"
    Then The returned json object will be:
         """
         {
           "completeTerm":"",
           "CatrobatInformation": {
             "BaseUrl":"http:\/\/localhost\/",
             "TotalProjects":0,
             "ProjectsExtension":".catrobat"
           },
           "CatrobatProjects":[],
           "preHeaderMessages":""
         }
         """