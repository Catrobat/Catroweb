@api
Feature: All uploaded projects have to be validated.

  Background: 
    Given the upload folder is empty
    And the extract folder is empty
    And there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
      | User1    | vwxyz    | aaaaaaaaaa |


  Scenario: project must have a code.xml
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with an missing code.xml
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":507,"answer":"unknown error: project_xml_not_found!","preHeaderMessages":""}
      """

  Scenario: project with missing images are rejected
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with a missing image
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":524,"answer":"Project XML metions a file which not exists in project-folder","preHeaderMessages":""}
      """

      
  Scenario: A valid file must contain at least one screenshot and image and sound directories

  Scenario: All media files except screenshots have to be defined in code.xml

  Scenario: A project must have a name.
