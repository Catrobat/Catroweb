@api @projects
Feature: Get recent projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | upload time      | FileSize | version | language version |   flavor    | upload_language |
      | 1  | project 1 |  Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |  pocketcode |      en         |
      | 2  | project 2 |  User1    | 50    | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      fr         |
      | 3  | project 3 |  Catrobat | 40    | 03.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |  pocketcode |      de         |
      | 4  | project 4 |  User2    | 50    | 04.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  luna       |      en         |
      | 5  | project 5 |  User1    | 40    | 05.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |      de         |
      | 6  | project 6 |  User1    | 50    | 02.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  luna       |      fr         |



  Scenario: Get recent projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?project_type=recent"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"User2",
            "description":"",
            "version":"0.8.5",
            "views": 50,
            "download": 0,
            "private":false,
            "flavor": "luna",
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
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": 10,
            "download": 0,
            "private":false,
            "flavor": "pocketcode",
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

  Scenario: Get recent projects in german and limit = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de"
    And I request "GET" "/api/projects/?project_type=recent&limit=1"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"User1",
            "description":"",
            "version":"0.8.5",
            "views": 40,
            "download": 0,
            "private":false,
            "flavor": "pocketcode",
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

  Scenario: Get recent projects in english with offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "en"
    And I request "GET" "/api/projects/?project_type=recent"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": 10,
            "download": 0,
            "private":false,
            "flavor": "pocketcode",
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

  Scenario: Get recent projects in french with max_version = 0.984
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "fr"
    And I request "GET" "/api/projects/?project_type=recent&max_version=0.984"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"User1",
            "description":"",
            "version":"0.8.5",
            "views": 50,
            "download": 0,
            "private":false,
            "flavor": "luna",
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

  Scenario: Get recent projects with flavor = luna
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/?project_type=recent&flavor=luna"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"User2",
            "description":"",
            "version":"0.8.5",
            "views": 50,
            "download": 0,
            "private":false,
            "flavor": "luna",
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