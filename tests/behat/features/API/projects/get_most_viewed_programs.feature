@project-api
Feature: Get the most viewed programs

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | catrobat | 123456   | cccccccccc | dev1@pocketcode.org | 1  |
      | User1    | 123456   | cccccccccc | dev2@pocketcode.org | 2  |

    And there are programs:
      | id | name      | description           | owned by | views | upload time      | FileSize | version | language version |
      | 1  | project 1 | project 1 description | catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |
      | 2  | project 2 | project 2 description | User1    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |
      | 3  | project 3 | project 3 description | User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |

  Scenario: show most viewed programs without skipping and the maximum number of results is 1
    Given I have a parameter "limit" with value "1"
    And I have a parameter "offset" with value "0"
    When I GET "/api/projects/mostViewed" with these parameters
    Then I should get the json object:
      """
      [
        {
            "id": "2",
            "name":"project 2",
            "author":"User1",
            "description":"project 2 description",
            "version":"0.8.5",
            "views": 50,
            "download": 1,
            "private":false,
            "flavor": {},
            "uploaded": 1406887200,
            "uploaded_string":"more than one year ago",
            "screenshot_large": "images/default/screenshot.png",
            "screenshot_small": "images/default/thumbnail.png",
            "projectUrl": "app/project/2",
            "downloadUrl": "app/download/2.catrobat",
            "filesize": 1
        }
      ]
      """

  Scenario: show most viewed programs skipping the first result
    And I have a parameter "offset" with value "1"
    When I GET "/api/projects/mostViewed" with these parameters
    Then I should get the json object:
      """
      [
        {
            "id": "3",
            "name":"project 3",
            "author":"User1",
            "description":"project 3 description",
            "version":"0.8.5",
            "views": 40,
            "download": 1,
            "private":false,
            "flavor": {},
            "uploaded": 1406887200,
            "uploaded_string":"more than one year ago",
            "screenshot_large": "images/default/screenshot.png",
            "screenshot_small": "images/default/thumbnail.png",
            "projectUrl": "app/project/3",
            "downloadUrl": "app/download/3.catrobat",
            "filesize": 1
        },
        {
            "id": "1",
            "name":"project 1",
            "author":"catrobat",
            "description":"project 1 description",
            "version":"0.8.5",
            "views": 10,
            "download": 1,
            "private":false,
            "flavor": {},
            "uploaded": 1406887200,
            "uploaded_string":"more than one year ago",
            "screenshot_large": "images/default/screenshot.png",
            "screenshot_small": "images/default/thumbnail.png",
            "projectUrl": "app/project/1",
            "downloadUrl": "app/download/1.catrobat",
            "filesize": 1
        }
      ]
      """

  Scenario: show most viewed programs, where the language version is limited by maxVersion.
    And I have a parameter "maxVersion" with value "0.123"
    When I GET "/api/projects/mostViewed" with these parameters
    Then I should get the json object:
      """
      [
        {
            "id": "3",
            "name":"project 3",
            "author":"User1",
            "description":"project 3 description",
            "version":"0.8.5",
            "views": 40,
            "download": 1,
            "private":false,
            "flavor": {},
            "uploaded": 1406887200,
            "uploaded_string":"more than one year ago",
            "screenshot_large": "images/default/screenshot.png",
            "screenshot_small": "images/default/thumbnail.png",
            "projectUrl": "app/project/3",
            "downloadUrl": "app/download/3.catrobat",
            "filesize": 1
        }
      ]
      """

  Scenario: Trying to call the api with invalid parameters
    Given I have a parameter "limit" with value "2"
    And I have a parameter "maxVersion" with value "0"
    When I GET "/api/projects/mostViewed" with these parameters
    Then The status code of the response should be "400"


  Scenario: Trying to call the api without sending the wanted accept header
    Given I have a parameter "limit" with value "2"
    When I GET "/api/projects/mostViewed" without the accept json header
    Then The status code of the response should be "406"
