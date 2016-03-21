@doc
Feature: Show featured programs

  Background:
    Given there are programs:
      | id | name         | owned by |
      | 1  | Invaders     | Catrobat |
      | 2  | Simple click | Catrobat |
      | 3  | A new world  | User1    |
      | 4  | Soon to be   | User1    |
    And following programs are featured:
      | name         |
      | A new world  |
      | Soon to be   |
    And the server name is "pocketcode.org"
      
      
  Scenario: Show featured programs
  
    Given The HTTP Request:
          | Method | GET                                    |
          | Url    | /pocketcode/api/projects/featured.json |
      And The GET parameters:
          | Name   | Value |
          | limit  | 10    |
          | offset | 0     |
     When The Request is invoked
     Then The returned json object will be:
          """
          {
            "CatrobatInformation": {
              "BaseUrl":"https://pocketcode.org/",
              "TotalProjects":2,
              "ProjectsExtension":".catrobat"
            },
            "CatrobatProjects": [{
              "ProjectId": 3,
              "ProjectName":"A new world",
              "FeaturedImage":"resources_test/featured/featured_1.jpg",
              "Author":"User1"
            },
            {
              "ProjectId": 4,
              "ProjectName":"Soon to be",
              "FeaturedImage": "resources_test/featured/featured_2.jpg",
              "Author":"User1"
            }
            ],
            "preHeaderMessages":""
          }
          """
          