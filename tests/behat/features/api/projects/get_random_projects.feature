@api @projects
Feature: Get random projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | downloads | upload time      | FileSize | version | language version |   flavor    | upload_language | visible |
      | 1  | project 1 |  Catrobat | 10    |    10     | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |  pocketcode |      en         |  true   |
      | 2  | project 2 |  User1    | 50    |     5     | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      fr         |  true   |
      | 3  | project 3 |  Catrobat | 50    |    40     | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |  pocketcode |      de         |  true   |
      | 4  | project 4 |  User2    | 50    |    20     | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      en         |  true   |
      | 5  | project 5 |  User1    | 40    |    10     | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |      de         |  true   |
      | 6  | project 6 |  User1    | 20    |    15     | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  luna       |      fr         |  true   |


  Scenario: Get random projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?project_type=random"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
          "id": "REGEX_STRING_WILDCARD",
          "name":"REGEX_STRING_WILDCARD",
          "author":"REGEX_STRING_WILDCARD",
          "description":"",
          "version":"REGEX_STRING_WILDCARD",
          "views": "REGEX_INT_WILDCARD",
          "download": "REGEX_INT_WILDCARD",
          "private":false,
          "flavor": "REGEX_STRING_WILDCARD",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/REGEX_STRING_WILDCARD",
          "download_url": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
          "filesize": 0
        },
        {
          "id": "REGEX_STRING_WILDCARD",
          "name":"REGEX_STRING_WILDCARD",
          "author":"REGEX_STRING_WILDCARD",
          "description":"",
          "version":"REGEX_STRING_WILDCARD",
          "views": "REGEX_INT_WILDCARD",
          "download": "REGEX_INT_WILDCARD",
          "private":false,
          "flavor": "REGEX_STRING_WILDCARD",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/REGEX_STRING_WILDCARD",
          "download_url": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
          "filesize": 0
        },
        {
          "id": "REGEX_STRING_WILDCARD",
          "name":"REGEX_STRING_WILDCARD",
          "author":"REGEX_STRING_WILDCARD",
          "description":"",
          "version":"REGEX_STRING_WILDCARD",
          "views": "REGEX_INT_WILDCARD",
          "download": "REGEX_INT_WILDCARD",
          "private":false,
          "flavor": "REGEX_STRING_WILDCARD",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/REGEX_STRING_WILDCARD",
          "download_url": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
          "filesize": 0
        },
        {
          "id": "REGEX_STRING_WILDCARD",
          "name":"REGEX_STRING_WILDCARD",
          "author":"REGEX_STRING_WILDCARD",
          "description":"",
          "version":"REGEX_STRING_WILDCARD",
          "views": "REGEX_INT_WILDCARD",
          "download": "REGEX_INT_WILDCARD",
          "private":false,
          "flavor": "REGEX_STRING_WILDCARD",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/REGEX_STRING_WILDCARD",
          "download_url": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
          "filesize": 0
        },
        {
          "id": "REGEX_STRING_WILDCARD",
          "name":"REGEX_STRING_WILDCARD",
          "author":"REGEX_STRING_WILDCARD",
          "description":"",
          "version":"REGEX_STRING_WILDCARD",
          "views": "REGEX_INT_WILDCARD",
          "download": "REGEX_INT_WILDCARD",
          "private":false,
          "flavor": "REGEX_STRING_WILDCARD",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/REGEX_STRING_WILDCARD",
          "download_url": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
          "filesize": 0
        },
        {
          "id": "REGEX_STRING_WILDCARD",
          "name":"REGEX_STRING_WILDCARD",
          "author":"REGEX_STRING_WILDCARD",
          "description":"",
          "version":"REGEX_STRING_WILDCARD",
          "views": "REGEX_INT_WILDCARD",
          "download": "REGEX_INT_WILDCARD",
          "private":false,
          "flavor": "REGEX_STRING_WILDCARD",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/REGEX_STRING_WILDCARD",
          "download_url": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
          "filesize": 0
        }
      ]
      """
