@api @projects
Feature: User public projects

  Background:
    Given there are users:
      | id        | name     | password |
      | user-1    | Catrobat | 123456   |
      | user-2    | User1    | 123456   |
      | user-3    | User2    | 123456   |
      | user-4    | User3    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | upload time      | FileSize | version | language version |   flavor    | private |
      | 1  | project 1 |  Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.999            |  pocketcode |  false  |
      | 2  | project 2 |  User1    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |    luna     |  false  |
      | 3  | project 3 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.123            |    luna     |  false  |
      | 4  | project 4 |  User2    | 50    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.984            |  pocketcode |  true   |
      | 5  | project 5 |  User1    | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |    luna     |  false  |
      | 6  | project 6 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |  true   |
      | 7  | project 7 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |  true   |
      | 8  | project 8 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |  true   |
      | 9  | project 9 |  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |  true   |
      | 10 | project 10|  Catrobat | 40    | 01.08.2014 12:00 | 1048576  | 0.8.5   | 0.985            |  pocketcode |  true   |


  Scenario: Get user public projects
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-1/"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
          "id": "1",
          "name": "project 1",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 10,
          "download": 0,
          "private": false,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/1",
          "download_url": "http://localhost/app/download/1.catrobat",
          "filesize": 0
        },
        {
          "id": "10",
          "name": "project 10",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 40,
          "download": 0,
          "private": true,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/10",
          "download_url": "http://localhost/app/download/10.catrobat",
          "filesize": 0
        },
        {
          "id": "3",
          "name": "project 3",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 40,
          "download": 0,
          "private": false,
          "flavor": "luna",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/3",
          "download_url": "http://localhost/app/download/3.catrobat",
          "filesize": 0
        },
        {
          "id": "6",
          "name": "project 6",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 40,
          "download": 0,
          "private": true,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/6",
          "download_url": "http://localhost/app/download/6.catrobat",
          "filesize": 0
        },
        {
          "id": "7",
          "name": "project 7",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 40,
          "download": 0,
          "private": true,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/7",
          "download_url": "http://localhost/app/download/7.catrobat",
          "filesize": 0
        },
        {
          "id": "8",
          "name": "project 8",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 40,
          "download": 0,
          "private": true,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/8",
          "download_url": "http://localhost/app/download/8.catrobat",
          "filesize": 0
        },
        {
          "id": "9",
          "name": "project 9",
          "author": "Catrobat",
          "description": "",
          "version": "0.8.5",
          "views": 40,
          "download": 0,
          "private": true,
          "flavor": "pocketcode",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string": "REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/9",
          "download_url": "http://localhost/app/download/9.catrobat",
          "filesize": 0
        }
      ]
      """

  Scenario: Get user public projects with limit = 1 and offset = 0
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-1/?limit=1&offset=0"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      [
        {
          "id": "1",
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

  Scenario: Get user public projects with limit = 1 and offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-2/?limit=1&offset=1"
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
          "flavor": "luna",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string":"REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/5",
          "download_url": "http://localhost/app/download/5.catrobat",
          "filesize": 0
        }
      ]
      """

  Scenario: Get user public projects with offset = 1
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-3/?offset=1"
    Then the response status code should be "200"
    Then I should get the json object:
      """
      []
      """

  Scenario: Get user public projects with maxVersion = 0.984
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-2/?max_version=0.984"
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
          "flavor": "luna",
          "uploaded": "REGEX_INT_WILDCARD",
          "uploaded_string":"REGEX_STRING_WILDCARD",
          "screenshot_large": "http://localhost/images/default/screenshot.png",
          "screenshot_small": "http://localhost/images/default/thumbnail.png",
          "project_url": "http://localhost/app/project/2",
          "download_url": "http://localhost/app/download/2.catrobat",
          "filesize": 0
        }
      ]
      """
  Scenario: Get user public projects with flavor = luna
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/projects/user/user-1/?flavor=luna"
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
          "views": 40,
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
