@api @projects @post
Feature: All uploaded programs have to be validated.

  Background:
    Given I am a valid user

  Scenario: program must have a code.xml
    When I upload a project with "a missing code.xml", API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

  Scenario: program must have a valid code.xml
    When I upload a project with "an invalid code.xml", API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

  @disabled
  Scenario: program with missing images are rejected
    When I upload a project with "a missing image", API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

  @disabled
  Scenario: program with media files not defined in xml are rejected
    When I upload a project with "an additional image", API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

  Scenario: invalid catrobat program files should be rejected
    When I upload an invalid project file, API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

  Scenario Outline: user should not be able to upload a program with an old pocketcode version
    Given I am using pocketcode for "<Platform>" with version "<Version>"
    When I upload a generated project, API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

    Examples:
      | Platform | Version |
      | Android  | 0.5     |
      | Windows  | 0.0.1   |
      | iOS      | 0.0.1   |


  Scenario Outline: user should not be able to upload a program with an old pocketcode version
    Given I am using pocketcode for "<Platform>" with version "<Version>"
    When I upload a generated project, API version 2
    Then the uploaded project should exist in the database, API version 2

    Examples:
      | Platform | Version |
      | Android  | 0.9.1   |
      | Android  | v0.9.1  |
      | Windows  | 0.1.0   |
      | iOS      | 0.1.0   |


  Scenario: user should not be able to upload a program with an old language version
    Given I am using pocketcode with language version "0.7"
    When I upload a generated project, API version 2
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """
