@api @projects
Feature: Logged in user projects

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
      | 3  | User2    | 123456   |
      | 4  | User3    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | upload time      | FileSize | version | language version |   flavor    |
      | 1  | project 1 |  Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |  pocketcode |
      | 2  | project 2 |  User1    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  pocketcode |
      | 3  | project 3 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |  pocketcode |
      | 4  | project 4 |  User2    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  pocketcode |
      | 5  | project 5 |  User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 6  | project 6 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 7  | project 7 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 8  | project 8 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 9  | project 9 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 10 | project 10|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 11 | project 11|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 12 | project 12|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 13 | project 13|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 14 | project 14|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 15 | project 15|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 16 | project 16|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 17 | project 17|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 18 | project 18|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 19 | project 19|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 20 | project 20|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 21 | project 21|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 22 | project 22|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 23 | project 23|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 24 | project 24|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 25 | project 25|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |
      | 26 | project 26|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  luna       |


  Scenario: Get projects without being logged in
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I request "GET" "/api/projects/user"
    Then the response status code should be "401"
    And I should get the json object:
    """
      {
        "code": 401,
        "message": "JWT Token not found"
      }
    """

  Scenario: Get logged in user projects
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?limit=2"
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
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
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
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        }
      ]
      """

  Scenario: Get logged in user projects with limit = 1 and offset = 0
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?limit=1&offset=0"
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
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        }
      ]
      """

  Scenario: Get logged in user projects with limit = 1 and offset = 1
    Given I use a valid JWT Bearer token for "User1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?limit=1&offset=1"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "5",
            "name":"project 5",
            "author":"User1",
            "description":"",
            "version":"0.8.5",
            "views": 40,
            "download": 0,
            "private":false,
            "flavor": "pocketcode",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string":"REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/5",
            "downloadUrl": "http://localhost/app/download/5.catrobat",
            "filesize": 0
        }
      ]
      """

  Scenario: Get logged in user projects with offset = 1
    Given I use a valid JWT Bearer token for "User2"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?offset=1"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      []
      """

  Scenario: Get logged in user projects with maxVersion = 0.984
    Given I use a valid JWT Bearer token for "User1"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?max_version=0.984"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "2",
            "name":"project 2",
            "author":"User1",
            "description":"",
            "version":"0.8.5",
            "views": 50,
            "download": 0,
            "private":false,
            "flavor": "pocketcode",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string":"REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/2",
            "downloadUrl": "http://localhost/app/download/2.catrobat",
            "filesize": 0
        }
      ]
      """
  Scenario: Get logged in user projects with flavor = luna
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/?flavor=luna"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
            "id": "26",
            "name":"project 26",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": 40,
            "download": 0,
            "private":false,
            "flavor": "luna",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string":"REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/26",
            "downloadUrl": "http://localhost/app/download/26.catrobat",
            "filesize": 0
        }
      ]
      """

   Scenario: Get logged in user projects with the default limit
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/"
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
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        },
        {
            "id": "REGEX_STRING_WILDCARD",
            "name":"REGEX_STRING_WILDCARD",
            "author":"Catrobat",
            "description":"",
            "version":"0.8.5",
            "views": "REGEX_INT_WILDCARD",
            "download": 0,
            "private":false,
            "flavor": "REGEX_STRING_WILDCARD",
            "uploaded": "REGEX_INT_WILDCARD",
            "uploaded_string": "REGEX_STRING_WILDCARD",
            "screenshot_large": "http://localhost/images/default/screenshot.png",
            "screenshot_small": "http://localhost/images/default/thumbnail.png",
            "projectUrl": "http://localhost/app/project/REGEX_STRING_WILDCARD",
            "downloadUrl": "http://localhost/app/download/REGEX_STRING_WILDCARD.catrobat",
            "filesize": 0
        }
      ]
      """
