@api @tag 
Feature: Upload a project with tag (Attention still the deprecated API!)

  Background:
    And I run the update tags command

  Scenario: get all tags default
    Given the current time is "24.09.2021 11:59"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":200,
        "constantTags":["game","animation","story","music","art","experimental","tutorial"]
      }
      """

  Scenario: get all tags during coding jam 09 2021 event
    Given the current time is "24.09.2021 12:00"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":200,
        "constantTags":["game","animation","story","music","art","experimental","tutorial","catrobatfestival2021"]
      }
      """

  Scenario: get all tags during coding jam 09 2021 event
    Given the current time is "27.09.2021 12:00"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":200,
        "constantTags":["game","animation","story","music","art","experimental","tutorial","catrobatfestival2021"]
      }
      """

  Scenario: get all tags after event
    Given the current time is "27.09.2021 12:01"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":200,
        "constantTags":["game","animation","story","music","art","experimental","tutorial"]
      }
      """