@api
Feature: Get featured programs

  Background:
    Given the server name is "pocketcode.org"
    And I use a secure connection
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc |  1 |
      | User1    | vwxyz    | aaaaaaaaaa |  2 |
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

  Scenario: show featured programs with limit 1 and offset 1
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "1"
    When I GET "/app/api/projects/featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatProjects":
          [
             {
              "ProjectId": "(.*?)",
              "ProjectName":"Soon to be",
              "Author":"User1",
              "FeaturedImage": "resources_test/featured/featured_3.jpg"
             }
          ],
         "preHeaderMessages":"",
         "CatrobatInformation":
          {
            "BaseUrl":"http://pocketcode.org/",
            "TotalProjects":"3",
            "ProjectsExtension":".catrobat"
          }
      }
      """


  Scenario: show featured programs with limit 10 and no offset
    Given I have a parameter "limit" with value "10"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatProjects":
          [
            {
              "ProjectId": "(.*?)",
              "ProjectName":"A new world",
              "Author":"User1",
              "FeaturedImage":"resources_test/featured/featured_2.jpg"
            },
            {
              "ProjectId": "(.*?)",
              "ProjectName":"Soon to be",
              "Author":"User1",
              "FeaturedImage": "resources_test/featured/featured_3.jpg"
            },
            {
              "ProjectId": "(.*?)",
              "ProjectName": "Invaders",
              "Author": "Catrobat",
              "FeaturedImage": "resources_test/featured/featured_1.jpg"
            }
          ],
         "preHeaderMessages":"",
         "CatrobatInformation":
          {
            "BaseUrl":"http://pocketcode.org/",
            "TotalProjects":"3",
            "ProjectsExtension":".catrobat"
          }
      }
      """

  Scenario: show featured programs with limit and offset
    Given I have a parameter "limit" with value "2"
    And I have a parameter "offset" with value "0"
    When I GET "/app/api/projects/featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatProjects":
          [
            {
              "ProjectId": "(.*?)",
              "ProjectName":"A new world",
              "Author":"User1",
              "FeaturedImage":"resources_test/featured/featured_2.jpg"
             },
             {
              "ProjectId": "(.*?)",
              "ProjectName":"Soon to be",
              "Author":"User1",
              "FeaturedImage": "resources_test/featured/featured_3.jpg"
             }
          ],
         "preHeaderMessages":"",
         "CatrobatInformation":
          {
            "BaseUrl":"http://pocketcode.org/",
            "TotalProjects":"3",
            "ProjectsExtension":".catrobat"
          }
      }
      """

  Scenario: show featured programs without limit and offset
    When I GET "/app/api/projects/featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatProjects":
          [
            {
              "ProjectId": "(.*?)",
              "ProjectName":"A new world",
              "Author":"User1",
              "FeaturedImage":"resources_test/featured/featured_2.jpg"
             },
             {
              "ProjectId": "(.*?)",
              "ProjectName":"Soon to be",
              "Author":"User1",
              "FeaturedImage": "resources_test/featured/featured_3.jpg"
             },
             {
              "ProjectId": "(.*?)",
              "ProjectName":"Invaders",
              "Author":"Catrobat",
              "FeaturedImage": "resources_test/featured/featured_1.jpg"
             }
          ],
         "preHeaderMessages":"",
         "CatrobatInformation":
          {
            "BaseUrl":"http://pocketcode.org/",
            "TotalProjects":"3",
            "ProjectsExtension":".catrobat"
          }
      }
      """

  Scenario: show featured programs without limit and offset
    When I GET "/app/api/projects/ios-featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatProjects":
          [
            {
              "ProjectId": "(.*?)",
              "ProjectName":"IOS test",
              "Author":"User1",
              "FeaturedImage":"resources_test/featured/featured_5.jpg"
             }
          ],
        "preHeaderMessages":"",
        "CatrobatInformation":
        {
          "BaseUrl":"http://pocketcode.org/",
          "TotalProjects":"1",
          "ProjectsExtension":".catrobat"
        }
      }
      """
