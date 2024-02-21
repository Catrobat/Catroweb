@api
Feature: Get users projects

  Get the projects from a specific user

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
      | NewUser  | 54321    | bbbbbbbbbb | 3  |
    And there are projects:
      | id | name            | description | owned by | downloads | views | upload time      | version |
      | 1  | Galaxy War      | p1          | User1    | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Minions         |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | Fisch           |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Ponny           | p2          | User1    | 245       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | MarkoTheBest    |             | NewUser  | 335       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Whack the Marko | Universe    | Catrobat | 2         | 33    | 01.02.2012 13:00 | 0.8.5   |
      | 7  | Superponny      | p1 p2 p3    | User1    | 4         | 33    | 01.01.2012 12:00 | 0.8.5   |
      | 8  | Universe        |             | User1    | 23        | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 9  | Webteam         |             | User1    | 100       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 10 | Fritz the Cat   |             | User1    | 112       | 33    | 01.01.2012 13:00 | 0.8.5   |
    And the current time is "01.08.2014 13:00"


  Scenario: show user projects
    Given I have a parameter "user_id" with value "1"
    When I GET "/app/api/projects/userProjects.json" with these parameters
    Then I should get the json object:
    """
      {
          "CatrobatProjects":[{
                                "ProjectId": "REGEX_STRING_WILDCARD",
                                "ProjectName":"Minions",
                                "ProjectNameShort":"Minions",
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
                            },
                            {
                                "ProjectId": "REGEX_STRING_WILDCARD",
                                "ProjectName":"Whack the Marko",
                                "ProjectNameShort":"Whack the Marko",
                                "Author":"Catrobat",
                                "Description":"Universe",
                                "Version":"0.8.5",
                                "Views":33,
                                "Downloads":2,
                                "Private":false,
                                "Uploaded": "REGEX_INT_WILDCARD",
                                "UploadedString":"more than one year ago",
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
                                   "TotalProjects":2,
                                   "ProjectsExtension":".catrobat"
                                  }
      }
      """

  Scenario: show one project from one user
    Given I have a parameter "user_id" with value "3"
    When I GET "/app/api/projects/userProjects.json" with these parameters
    Then I should get projects in the following order:
      | Name         |
      | MarkoTheBest |

  Scenario: empty result set is returend if the user doesnt exist or has no projects
    Given I have a parameter "user_id" with value "5"
    When I GET "/app/api/projects/userProjects.json" with these parameters
    Then I should get projects in the following order:
      | Name |

  Scenario: show only visible projects
    Given project "MarkoTheBest" is not visible
    And I have a parameter "user_id" with value "3"
    When I GET "/app/api/projects/userProjects.json" with these parameters
    Then I should get projects in the following order:
      | Name |
