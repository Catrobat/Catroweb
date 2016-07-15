@api
Feature: Checking for rude words

  Background:
    Given I define the following rude words:
      | word     |
      | fuck     |
      | assbite  |

  Scenario: upload a program with a rude word in description should be rejected
    Given I am a valid user
    And I have a program with "assbite" as name
    When I upload this program
    Then I should get the json object:
    """
    {"statusCode":511,"answer":"Programname must not contain rude wordes.","preHeaderMessages":""}
    """
    
  Scenario: upload a program with a rude word in description should be rejected
    Given I am a valid user
    And I have a program with "assbite" as description
    When I upload this program
    Then I should get the json object:
      """
      {"statusCode":512,"answer":"Description must not contain rude wordes.","preHeaderMessages":""}
      """
    
  Scenario Outline: a program with a rude word in description should be rejected
    Given I am a valid user
    And I have a program with "<description>" as description
    When I upload this program
    Then the program should get <accepted or rejected>
    
    Examples:
    | description     | accepted or rejected   |
    | assbite world   | rejected               |
    | my little pony  | accepted               |
    | assbiter        | accepted               |
    
    