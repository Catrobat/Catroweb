@api
Feature: Upload a program 

  Background: 
    Given the upload folder is empty
    And the extract folder is empty
    And there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: program upload with valid data
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file
    And I have a parameter "fileChecksum" with the md5checksum of "test.catrobat"
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object with random "token" and "projectId":
      """
      {"projectId":"","statusCode":200,"answer":"Your project was uploaded successfully!","token":"","preHeaderMessages":""}
      """
    And the returned "projectId" should be a number

  Scenario: missing all prameters will result in an error
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":501,"answer":"POST-Data not correct or missing!","preHeaderMessages":""}
      """
  Scenario: trying to upload with an invalid user should result in an error
    Given I have a parameter "username" with value "INVALID"
    And I have a parameter "token" with value "cccccccccc"
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":601,"answer":"Authentication of device failed: invalid auth-token!","preHeaderMessages":""}
      """
    
  Scenario: trying to upload with an invalid token should result in an error
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "INVALID"
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":601,"answer":"Authentication of device failed: invalid auth-token!","preHeaderMessages":""}
      """

  Scenario: trying to upload with a missing token should result in an error
    Given I have a parameter "username" with value "Catrobat"
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":601,"answer":"Authentication of device failed: invalid auth-token!","preHeaderMessages":""}
      """

  Scenario: uploading the same program again should result in an update

    