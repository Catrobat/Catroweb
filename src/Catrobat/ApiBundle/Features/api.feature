@api
Feature: Pocketcode API

  Background: 
    Given a User named "Catrobat" with password "12345" and token "cccccccccc"
    And a project named "catrobat test project" owned by "Catrobat"

  Scenario: Checking the current token
    Given I am Catrobat
    When I call "checkToken" with token "cccccccccc"
    Then I should see:
    """
    {"statusCode":200,"answer":"ok","preHeaderMessages":"  \n"}
    """
    
  Scenario: Checking an invalid token
    Given I am Catrobat
    When I call "checkToken" with token "invalid"
    Then I should see:
    """
    {"statusCode":601,"answer":"Sorry, your authentication data was incorrect. Please check your nickname and password!","preHeaderMessages":"  \n"}
    """

    