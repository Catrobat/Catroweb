@api
Feature: Checking for badwords

#  Background:
#    Given the upload folder is empty
#    And the extract folder is empty
#    And there are users:
#      | name     | password | token      |
#      | Catrobat | 12345    | cccccccccc |


  Scenario: upload a program with a rude word should be rejected
    Given I am a valid user
    When I upload a program with a rude word in the description
    Then I should get the json object:
      """
      {"statusCode":512,"answer":"rude word detected!","preHeaderMessages":""}
      """
