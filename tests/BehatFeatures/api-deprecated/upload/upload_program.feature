@api @upload
Feature: Upload a program

  Background:
    Given there are users:
      | name     | password | token      | id |
      | Catrobat | 12345    | cccccccccc | 1  |
      | User1    | vwxyz    | aaaaaaaaaa | 2  |
    And there are projects:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 33        | 9     | 01.02.2013 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: program upload with valid data
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file, API version 1
    And I have a parameter "fileChecksum" with the md5checksum of "test.catrobat"
    When I POST these parameters to "/app/api/upload/upload.json"
    Then I should get the json object:
      """
      {"projectId":"REGEX_STRING_WILDCARD","statusCode":200,"answer":"Your project was uploaded successfully!","token":"REGEX_STRING_WILDCARD","preHeaderMessages":""}
      """

  Scenario: missing all prameters will result in an error
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    When I POST these parameters to "/app/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":501,"answer":"POST-data not correct or missing!","preHeaderMessages":""}
      """

  Scenario: trying to upload with an invalid user is okay, only token is used
    Given I have a parameter "username" with value "INVALID"
    And I have a parameter "token" with value "cccccccccc"
    When I POST these parameters to "/app/api/upload/upload.json"
    Then the response code should be "200"

  Scenario: trying to upload with an invalid token should result in an error
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "INVALID"
    When I POST these parameters to "/app/api/upload/upload.json"
    Then the response code should be "403"

  Scenario: trying to upload with a missing token should result in an error
    Given I have a parameter "username" with value "Catrobat"
    When I POST these parameters to "/app/api/upload/upload.json"
    Then the response code should be "401"

  Scenario: uploading the same project again should result in an update
    Given I am "Catrobat"
    When I upload a valid Catrobat project, API version 1
    And I upload a valid Catrobat project with the same name, API version 1
    Then the uploaded project should exist in the database, API version 1
    And it should be updated, API version 1

  Scenario: program with missing file checksum are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file, API version 1
    When I POST these parameters to "/app/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":503,"answer":"Client did not send fileChecksum! Are you using an outdated version of Pocket Code?","preHeaderMessages":""}
      """

  Scenario: program with invalid file checksum are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file, API version 1
    And I have a parameter "fileChecksum" with an invalid md5checksum of my file
    When I POST these parameters to "/app/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":504,"answer":"invalid checksum","preHeaderMessages":""}
      """

  Scenario: Token is not changing on upload
    Given the next generated token will be "aabbccddee"
    And I am "Catrobat"
    And I upload a valid Catrobat project, API version 1
    When I upload another project using token "cccccccccc"
    Then the uploaded project should exist in the database, API version 1

  Scenario: Program Sanitizer should remove unnecessary files
    Given I try to upload a project with unnecessary files, API version 1
    Then the uploaded project should exist in the database, API version 1
    And the resources should not contain the unnecessary files

  Scenario: Program Sanitizer should remove unnecessary files even when scenes are used
    Given I try to upload a project with scenes and unnecessary files, API version 1
    Then the uploaded project should exist in the database, API version 1
    And the resources should not contain the unnecessary files
