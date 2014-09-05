@api
Feature: All uploaded programs have to be validated.

  Background: 
    Given the upload folder is empty
    And the extract folder is empty
    And there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |


  Scenario: program must have a code.xml
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with a missing code.xml
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":507,"answer":"unknown error: project_xml_not_found!","preHeaderMessages":""}
      """

  Scenario: program must have a valid code.xml
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with an invalid code.xml
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":508,"answer":"invalid code xml","preHeaderMessages":""}
      """


  Scenario: program with missing images are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with a missing image
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":524,"answer":"Project XML mentions a file which does not exist in project-folder","preHeaderMessages":""}
      """

  Scenario: program with missing file checksum are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":503,"answer":"Client did not send fileChecksum! Are you using an outdated version of Pocket Code?","preHeaderMessages":""}
      """

  Scenario: program with invalid file checksum are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file
    And I have a parameter "fileChecksum" with an invalid md5checksum of my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":504,"answer":"invalid checksum","preHeaderMessages":""}
      """

  Scenario: program with media files not defined in xml are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with an additional image
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":525,"answer":"unexpected file found","preHeaderMessages":""}
      """

  Scenario: invalid catrobat program files should be rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have an invalid Catrobat file
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":505,"answer":"invalid file","preHeaderMessages":""}
      """

      
  Scenario: A valid file must contain at least one screenshot and image and sound directories

  Scenario: All media files except screenshots have to be defined in code.xml

  Scenario: A program must have a name.
