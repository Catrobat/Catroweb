@api @projects
Feature: Get project

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | User1    | 123456   |
    And there are programs:
      | id | name      |  owned by | views | upload time      | FileSize | version |   flavor    |  private | visible |
      | 1  | project 1 |  Catrobat | 10    | 01.08.2014 12:00 | 1048576  | 0.8.5   |  pocketcode |   false  |  true   |
      | 2  | project 2 |  User1    | 50    | 02.08.2014 12:00 | 1048576  | 0.8.5   |  luna       |   false  |  true   |
      | 3  | project 3 |  Catrobat | 40    | 03.08.2014 12:00 | 1048576  | 0.8.5   |  pocketcode |   true   |  true   |
      | 4  | project 4 |  Catrobat | 40    | 03.08.2014 12:00 | 1048576  | 0.8.5   |  pocketcode |   true   |  false  |

  Scenario: Get specific project
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/1"
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
        }
      ]
   """

  Scenario: Get specific project
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/2"
    Then the response status code should be "200"
    Then I should get the json object:
    """
      [
        {
          "id": "2",
          "name": "project 2",
          "author": "User1",
          "description": "",
          "version": "0.8.5",
          "views": 50,
          "download": 0,
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

  Scenario: Accessing private project must be not possible
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/3"
    Then the response status code should be "200"
    Then I should get the json object:
    """
      []
    """

  Scenario: Accessing hidden project must be not possible
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/4"
    Then the response status code should be "200"
    Then I should get the json object:
    """
      []
    """

  Scenario: Get specific project with no existing id
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/project/5"
    Then the response status code should be "200"
    Then I should get the json object:
    """
      []
    """

  Scenario: Get specific project without accept header
    And I request "GET" "/api/project/1"
    Then the response status code should be "406"