@api
Feature: Pocketcode API

  Background: 
    Given there are users:
      | name       | password | token        |
      | "Catrobat" | "12345"  | "cccccccccc" |
      | "User1"    | "vwxyz"  | "aaaaaaaaaa" |
    And there are projects:
      | id | name        | description | owned by   | downloads | views | upload time      | version |
      | 1  | "project 1" | "p1"        | "Catrobat" | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | "project 2" | ""          | "Catrobat" | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | "project 3" | ""          | "User1"    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  ####### check token #######
  Scenario: Checking the current token
    Given I am "Catrobat"
    When I call "/api/checkToken/check.json" with token "cccccccccc"
    Then I should see:
      """
      {"statusCode":200,"answer":"ok","preHeaderMessages":"  \n"}
      """

  Scenario: Checking an invalid token
    Given I am "Catrobat"
    When I call "/api/checkToken/check.json" with token "invalid"
    Then I should see:
      """
      {"statusCode":601,"answer":"Sorry, your authentication data was incorrect. Please check your nickname and password!","preHeaderMessages":"  \n"}
      """

  ####### login or register #######
  @TODO
  Scenario: loginOrRegister with new user
    Given I am not registered
    And I have a username "newuser"
    And I have a password "newpassword"
    And I have a language "at"
    And I have an email address "test@wherever.com"
    When I call "/api/loginOrRegister/loginOrRegister.json" the given data
    Then I should see:
      """
      {"token":"<token>","statusCode":201,"answer":"Registration successful!","preHeaderMessages":""}
      """

  @TODO
  Scenario: loginOrRegister with valid login
    Given I am "Catrobat"
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "Catrobat" and password "12345"
    Then I should see:
      """
      {"token":"cccccccccc","statusCode":200,"preHeaderMessages":""}
      """

  @TODO
  Scenario: loginOrRegister with invalid login
    Given I am "Catrobat"
    When I call "/api/loginOrRegister/loginOrRegister.json" with username "Catrobat" and password "invalid"
    Then I should see:
      """
      {"statusCode":601,"answer":"The password or username was incorrect.","preHeaderMessages":""}
      """

  ####### project upload #######
  @TODO
  Scenario: project upload with valid data
    Given I have the username "Catrobat"
    And I have a token "cccccccccc"
    And I have a file "test.catrobat"
    And I have the md5sum of "test.catrobat"
    When I call "/api/upload/upload.json" with the given data
    Then I should see:
      """
      {"projectId":<id>,"statusCode":200,"answer":"Your project was uploaded successfully!","token":<token>,"preHeaderMessages":""}
      """

  ####### project search #######
  @TODO
  Scenario: search projects
    Given I want to search for the term "project"
    And I have the limit "1"
    And I have the offset "0"
    When I call "/api/projects/search.json" with the given data
    Then I should see:
      """
      {
	      "completeTerm":"",
	      "CatrobatInformation": {
	                               "BaseUrl":"https:\/\/localhost\/",
	                               "TotalProjects":3,
	                               "ProjectsExtension":".catrobat"
	                              },
	      "CatrobatProjects":[{
	                            "ProjectId":"1",
	                            "ProjectName":"project 1",
	                            "ProjectNameShort":"project 1",
	                            "ScreenshotBig":"resources\/thumbnails\/1_large.png",
												      "ScreenshotSmall":"resources\/thumbnails\/1_small.png",
												      "Author":"Catrobat",
												      "Description":"",
												      "Uploaded":<time>,
												      "UploadedString":<timestring>,
												      "Version":"0.8.5",
												      "Views":"12",
												      "Downloads":"3",
												      "ProjectUrl":"details\/1",
												      "DownloadUrl":"download\/1.catrobat"
												    }],
	      "preHeaderMessages":""
      }
      """

  ####### recent projects #######
  @TODO
  Scenario: show recent projects
    Given I have the limit "1"
    And I have the offset "0"
    When I call "/api/projects/recent.json" with the given data
    Then I should see:
      """
      {
          "completeTerm":"",
          "CatrobatInformation": {
                                   "BaseUrl":"https:\/\/localhost\/",
                                   "TotalProjects":3,
                                   "ProjectsExtension":".catrobat"
                                  },
          "CatrobatProjects":[{
                                "ProjectId":"1",
                                "ProjectName":"project 1",
                                "ProjectNameShort":"project 1",
                                "ScreenshotBig":"resources\/thumbnails\/1_large.png",
                                                      "ScreenshotSmall":"resources\/thumbnails\/1_small.png",
                                                      "Author":"Catrobat",
                                                      "Description":"",
                                                      "Uploaded":<time>,
                                                      "UploadedString":<timestring>,
                                                      "Version":"0.8.5",
                                                      "Views":"12",
                                                      "Downloads":"3",
                                                      "ProjectUrl":"details\/1",
                                                      "DownloadUrl":"download\/1.catrobat"
                                                    }],
          "preHeaderMessages":""
      }
      """

 