@api
Feature: Get featured programs

  Background: 
    Given the server name is "pocketcode.org"
    And I use a secure connection
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are programs:
      | id | name         | description | owned by | downloads | views | upload time      | version |
      | 1  | Invaders     | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Simple click |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | A new world  |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Soon to be   |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | IOS test     |             | User1    | 0         | 51    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Mega Game1   |             | User1    | 22        | 78    | 01.01.2012 13:00 | 0.8.5   |
    And following programs are featured:
      | name         | active | priority | ios_only |
      | Invaders     | yes    | 1        | no       |
      | A new world  | yes    | 3        | no       |
      | Soon to be   | yes    | 2        | no       |
      | Simple click | no     | 4        | no       |
      | IOS test     | yes    | 0        | yes      |
      | Mega Game1   | no     | 1        | yes      |
      
  Scenario: show featured programs with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    When I GET "/pocketcode/api/projects/featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatInformation":
            {
                "BaseUrl":"https://pocketcode.org/",
                "TotalProjects":3,
                "ProjectsExtension":".catrobat"
            },
        "CatrobatProjects":
            [
                {
                    "ProjectId": 3,
                    "ProjectName":"A new world",
                    "FeaturedImage":"resources_test/featured/featured_2.jpg",
                    "Author":"User1"
                 },
                 {
                    "ProjectId": 4,
                    "ProjectName":"Soon to be",
                    "FeaturedImage": "resources_test/featured/featured_3.jpg",
                    "Author":"User1"
                 }
            ],
         "preHeaderMessages":""
      }
      """

  Scenario: show featured programs without limit and offset
    When I GET "/pocketcode/api/projects/featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatInformation":
            {
                "BaseUrl":"https://pocketcode.org/",
                "TotalProjects":3,
                "ProjectsExtension":".catrobat"
            },
        "CatrobatProjects":
            [
                {
                    "ProjectId": 3,
                    "ProjectName":"A new world",
                    "FeaturedImage":"resources_test/featured/featured_2.jpg",
                    "Author":"User1"
                 },
                 {
                    "ProjectId": 4,
                    "ProjectName":"Soon to be",
                    "FeaturedImage": "resources_test/featured/featured_3.jpg",
                    "Author":"User1"
                 },
                 {
                    "ProjectId": 1,
                    "ProjectName":"Invaders",
                    "FeaturedImage": "resources_test/featured/featured_1.jpg",
                    "Author":"Catrobat"
                 }
            ],
         "preHeaderMessages":""
      }
      """

  Scenario: show featured programs without limit and offset
    When I GET "/pocketcode/api/projects/ios-featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatInformation":
            {
                "BaseUrl":"https://pocketcode.org/",
                "TotalProjects":1,
                "ProjectsExtension":".catrobat"
            },
        "CatrobatProjects":
            [
                {
                    "ProjectId": 5,
                    "ProjectName":"IOS test",
                    "FeaturedImage":"resources_test/featured/featured_5.jpg",
                    "Author":"User1"
                 }
            ],
         "preHeaderMessages":""
      }
      """
