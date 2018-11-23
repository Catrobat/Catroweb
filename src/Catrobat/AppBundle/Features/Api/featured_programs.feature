@api
Feature: List featured programs

  Background:
    Given there are programs:
      | id | name         | owned by |
      | 1  | Invaders     | Catrobat |
      | 2  | Simple click | Catrobat |
      | 3  | A new world  | User1    |
      | 4  | Soon to be   | User1    |
    And following programs are featured:
      | name        |
      | A new world |
      | Soon to be  |
    And the server name is "pocketcode.org"


  Scenario: List featured programs

    Given the HTTP Request:
      | Method | GET                                    |
      | Url    | /pocketcode/api/projects/featured.json |
    And the GET parameters:
      | Name   | Value |
      | limit  | 10    |
      | offset | 0     |
    When the Request is invoked
    Then the returned json object will be:
      """
      {
        "CatrobatProjects": [{
          "ProjectId": 3,
          "ProjectName":"A new world",
          "Author":"User1",
          "FeaturedImage":"resources_test/featured/featured_1.jpg"
        },
        {
          "ProjectId": 4,
          "ProjectName":"Soon to be",
          "Author":"User1",
          "FeaturedImage": "resources_test/featured/featured_2.jpg"
        }],
        "preHeaderMessages":"",
        "CatrobatInformation": {
          "BaseUrl":"https://pocketcode.org/",
          "TotalProjects": "2",
          "ProjectsExtension":".catrobat"
        }
      }
      """

  Scenario: Limit returned list

    Given the HTTP Request:
      | Method | GET                                    |
      | Url    | /pocketcode/api/projects/featured.json |
    And the GET parameters:
      | Name   | Value |
      | limit  | 1     |
      | offset | 1     |
    When the Request is invoked
    Then the returned json object will be:
      """
      {
        "CatrobatProjects": [{
          "ProjectId": 4,
          "ProjectName":"Soon to be",
          "FeaturedImage": "resources_test/featured/featured_2.jpg",
          "Author":"User1"
        }
        ],
        "preHeaderMessages":"",
        "CatrobatInformation": {
          "BaseUrl":"https://pocketcode.org/",
          "TotalProjects": "2",
          "ProjectsExtension":".catrobat"
        }
      }
      """
