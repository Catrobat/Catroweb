@api @projects
Feature: Get most downloaded projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | downloads | upload time      | FileSize | version | language version |   flavor    | upload_language |
      | 1  | project 1 |  Catrobat | 10    |    10     | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |  pocketcode |      en         |
      | 2  | project 2 |  User1    | 50    |     5     | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      fr         |
      | 3  | project 3 |  Catrobat | 50    |    40     | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |  pocketcode |      de         |
      | 4  | project 4 |  User2    | 50    |    20     | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      en         |
      | 5  | project 5 |  User1    | 40    |    10     | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |      de         |
      | 6  | project 6 |  User1    | 20    |    15     | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  luna       |      fr         |
    And following projects are examples:
      | name      | active | priority |
      | project 1 | 0      | 1        |
      | project 2 | 1      | 2        |
      | project 3 | 1      | 3        |


  Scenario: Get example projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?project_type=example"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
          "id": "3",
          "name": "project 3",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 50,
          "download": 40,
          "private": false,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/3",
          "download_url": "http://localhost/app/download/3.catrobat",
          "filesize": 0
        },
                {
          "id": "2",
          "name": "project 2",
          "author": "User1",
          "description": "",
          "version": "0.8.5",
          "views": 50,
          "download": 5,
          "private": false,
          "flavor": "luna",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/2",
          "download_url": "http://localhost/app/download/2.catrobat",
          "filesize": 0
        }
      ]
      """