@api
Feature: Search programs

    To find programs, users should be able to search all available programs for specific words 

  Background: 
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
      | NewUser  | 54321    | bbbbbbbbbb |
    And there are programs:
      | id | name             | description | owned by | downloads | views | upload time      | version |
      | 1  | Galaxy War       | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Minions          |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | Fisch            |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Ponny            | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | MarkoTheBest     |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Whack the Marko  |             | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
      | 7  | Superponny       | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
      | 8  | Universe         |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | Webteam          |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | Fritz the Cat    |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |



  Scenario: A request must have specific parameters to succeed
  
    Given I have a parameter "q" with value "Galaxy"
    And I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/api/projects/search.json" with these parameters
    Then I should get following programs:
        | Name          |
        | Galaxy War    |



  Scenario: The result is in JSON format
  
    When I search for "Galaxy"
    Then I should get the json object:
    """
    {
      "completeTerm":"",
      "CatrobatInformation": {
           "BaseUrl":"https:\/\/localhost\/",
         "TotalProjects":1,
         "ProjectsExtension":".catrobat"
     },
     "CatrobatProjects":[{
         "ProjectId":1,
         "ProjectName":"Galaxy War",
         "ProjectNameShort":"Galaxy War",
         "ScreenshotBig":"resources\/thumbnails\/1_large.png",
         "ScreenshotSmall":"resources\/thumbnails\/1_small.png",
         "Author":"User1",
         "Description":"p1",
         "Uploaded":1357041600,
         "UploadedString":"0",
         "Version":"0.8.5",
         "Views":"12",
         "Downloads":"3",
         "ProjectUrl":"details\/1",
         "DownloadUrl":"download\/1.catrobat"
     }],
    "preHeaderMessages":""
    }
    """



  Scenario: Search for a program with a certain name
    
    When I search for "Minions"
    Then I should get following programs:
        | Name          |
        | Minions       |



  Scenario: The results can be limited and offset
  
    Given I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I search for "marko"
    Then I should get following programs:
        | Name                  |
        | Whack the Marko       |
        | MarkoTheBest          |



  Scenario: If a user is found with the given search query, return her programs
  
    When I search for "NewUser"
    Then I should get following programs:
        | Name                  |
        | MarkoTheBest          |



  Scenario: If nothing is found the following JSON is returned
  
    When I search for "NOTHINGTOBEFIOUND"
    Then I should get the json object:
    """
    {
      "completeTerm":"",
      "CatrobatInformation": {
        "BaseUrl":"https:\/\/localhost\/",
        "TotalProjects":1,
        "ProjectsExtension":".catrobat"
    },
    "CatrobatProjects":[],
    "preHeaderMessages":""
    }
    """



  Scenario: search program by description
  
    Given I use the limit "10"
    When I search for "p1"
    Then I should get following programs:
        | Name                |
        | Galaxy War          |
        | Superponny          |



  Scenario: search program by description

    Given I use the limit "10"
    When I search for "p2"
    Then I should get following programs:
        | Name                |
        | Ponny               |
        | Superponny          |

#
#
#  @TODO
#  Scenario: search all programs
#    Given I have a parameter "projectName" with value "program 1"
#    And I have a parameter "limit" with value "1"
#    And I have a parameter "offset" with value "0"
#    When I GET "/api/projects/search.json" with these parameters
#    Then I should get the json object:
#"""
#{
#  "ProgrammCount":"10";
#}
#"""
#
#
#
#
#  @TODO
#  Scenario: search programs by name and with a limit of 5 and offset of 0
#    Given I have a parameter "projectName" with value "program 1"
#    And I have a parameter "limit" with value "5"
#    And I have a parameter "offset" with value "0"
#    When I POST these parameters to "/api/projects/search.json"
#    Then I should get the json object:
#"""
#{
#  "completeTerm":"",
#  "CatrobatInformation": {
#		"BaseUrl":"https:\/\/localhost\/",
#		"TotalProjects":3,
#		"ProjectsExtension":".catrobat"
#  },
#  "CatrobatProjects":[{
#		"ProjectId":"1",
#		"ProjectName":"program 1",
#		"ProjectNameShort":"program 1",
#		"ScreenshotBig":"resources\/thumbnails\/1_large.png",
#		"ScreenshotSmall":"resources\/thumbnails\/1_small.png",
#		"Author":"Catrobat",
#		"Description":"p1",
#		"Uploaded":"1357041600",
#		"UploadedString":<timestring>,
#		"Version":"0.8.5",
#		"Views":"12",
#		"Downloads":"3",
#		"ProjectUrl":"details\/1",
#		"DownloadUrl":"download\/1.catrobat"
#  }],
#  "preHeaderMessages":""
#}
#"""
#
#
#  @TODO
#  Scenario: search programs by name and with a limit of 5 and offset of 2
#    Given I have a parameter "projectName" with value "program 1"
#    And I have a parameter "limit" with value "5"
#    And I have a parameter "offset" with value "2"
#    When I POST these parameters to "/api/projects/search.json"
#    Then I should get the json object:
#"""
#{
#  "completeTerm":"",
#  "CatrobatInformation": {
#		"BaseUrl":"https:\/\/localhost\/",
#		"TotalProjects":3,
#		"ProjectsExtension":".catrobat"
#  },
#  "CatrobatProjects":[{
#		"ProjectId":"1",
#		"ProjectName":"program 1",
#		"ProjectNameShort":"program 1",
#		"ScreenshotBig":"resources\/thumbnails\/1_large.png",
#		"ScreenshotSmall":"resources\/thumbnails\/1_small.png",
#		"Author":"Catrobat",
#		"Description":"p1",
#		"Uploaded":"1357041600",
#		"UploadedString":<timestring>,
#		"Version":"0.8.5",
#		"Views":"12",
#		"Downloads":"3",
#		"ProjectUrl":"details\/1",
#		"DownloadUrl":"download\/1.catrobat"
#  }],
#  "preHeaderMessages":""
#}
#"""
#
#
#
#  @TODO
#  Scenario: search all users
#    Given I have a parameter "projectName" with value "program 1"
#    And I have a parameter "limit" with value "1"
#    And I have a parameter "offset" with value "0"
#    When I POST these parameters to "/api/projects/search.json"
#    Then I should get the json object:
#    """
#{
#  "UserCount":"2";
#}
#"""
#
#  @TODO
#  Scenario: search user by name
#    Given I have a parameter "Author" with value "User1"
#    And I have a parameter "limit" with value "1"
#    And I have a parameter "offset" with value "0"
#    When I POST these parameters to "/api/projects/search.json"
#    Then I should get the json object:
#"""
#{
#"Author":"User1";
#}
#"""



