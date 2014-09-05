@api
Feature: Checking for badwords

  Background: 
    Given the upload folder is empty
    And the extract folder is empty
    And there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |


  Scenario: program must have a code.xml
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a Catrobat file with an bad word in the description
    And I have a parameter "fileChecksum" with the md5checksum my file
    When I POST these parameters to "/api/upload/upload.json"
    Then I should get the json object:
      """
      {"statusCode":801,"answer":"insulting word","preHeaderMessages":""}
      """
