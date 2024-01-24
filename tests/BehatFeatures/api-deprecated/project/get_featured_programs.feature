@api
Feature: Get featured ios programs (deprecated)

  Background:
    Given the server name is "pocketcode.org"
    And I use a secure connection
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are projects:
      | id | name         | description | owned by | downloads | views | upload time      | version |
      | 1  | Invaders     | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | Simple click |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | A new world  |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 4  | Soon to be   |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |
      | 5  | IOS test     |             | User1    | 0         | 51    | 01.01.2012 13:00 | 0.8.5   |
      | 6  | Mega Game1   |             | User1    | 22        | 78    | 01.01.2012 13:00 | 0.8.5   |
    And following projects are featured:
      | name         | active | priority | ios_only |
      | Invaders     | 1      | 1        | no       |
      | A new world  | 1      | 3        | no       |
      | Soon to be   | 1      | 2        | no       |
      | Simple click | 0      | 4        | no       |
      | IOS test     | 1      | 0        | yes      |
      | Mega Game1   | 0      | 1        | yes      |

  Scenario: show featured programs without limit and offset
    When I GET "/app/api/projects/ios-featured.json" with these parameters
    Then I should get the json object:
      """
      {
        "CatrobatProjects":
          [
            {
              "ProjectId": "REGEX_STRING_WILDCARD",
              "ProjectName":"IOS test",
              "Author":"User1",
              "FeaturedImage":"resources_test/featured/featured_5.jpg"
             }
          ],
        "preHeaderMessages":"",
        "CatrobatInformation":
        {
          "BaseUrl":"http://pocketcode.org/",
          "TotalProjects":1,
          "ProjectsExtension":".catrobat"
        }
      }
      """
