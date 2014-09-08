@api
Feature: Checking for rude words

  Background:
    Given I define the following rude words:
      | word     |
      | fuck     |
      | assbite  |

  Scenario: upload a program with a rude word should be rejected
    Given I am a valid user
    When I upload a program with a rude word in the description
    Then I should get the json object:
      """
      {"statusCode":512,"answer":"Description must not contain rude wordes.","preHeaderMessages":""}
      """

  Scenario: upload a program with a rude word should be rejected
    Given I am a valid user
    When I upload a program with a rude word in the name
    Then I should get the json object:
    """
    {"statusCode":511,"answer":"Programname must not contain rude wordes.","preHeaderMessages":""}
    """
