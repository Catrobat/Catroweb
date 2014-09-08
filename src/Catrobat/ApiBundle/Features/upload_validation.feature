@api
Feature: All uploaded programs have to be validated.

  Background: 
    Given I am a valid user

  Scenario: program must have a code.xml
    When I upload a program with a missing code.xml
    Then I should get the json object:
      """
      {"statusCode":507,"answer":"unknown error: project_xml_not_found!","preHeaderMessages":""}
      """

  Scenario: program must have a valid code.xml
    When I upload a program with an invalid code.xml
    Then I should get the json object:
      """
      {"statusCode":508,"answer":"invalid code xml","preHeaderMessages":""}
      """

  Scenario: program with missing images are rejected
    When I upload a program with a missing image
    Then I should get the json object:
      """
      {"statusCode":524,"answer":"Project XML mentions a file which does not exist in project-folder","preHeaderMessages":""}
      """

  Scenario: program with media files not defined in xml are rejected
    When I upload a program with an additional image
    Then I should get the json object:
      """
      {"statusCode":525,"answer":"unexpected file found","preHeaderMessages":""}
      """

  Scenario: invalid catrobat program files should be rejected
    When I upload an invalid program file
    Then I should get the json object:
      """
      {"statusCode":505,"answer":"invalid file","preHeaderMessages":""}
      """

      
  Scenario: A valid file must contain at least one screenshot and image and sound directories

  Scenario: All media files except screenshots have to be defined in code.xml

  Scenario: A program must have a name.
