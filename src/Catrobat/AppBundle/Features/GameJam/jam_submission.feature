Feature: Submitting games to a game jam

#Scenario: Google Form#
# ID, Username, email 

#ongoing = start submittion until winners  etc ..
# submitted
# acceppted

Scenario: Submitting a game
    Given there is an ongoing game jam
    When I submit a game
    Then It should be accepted
    And I should get the url to the google form

Scenario: Resubmitting a game
          Google form submitted
 
    Given there is an ongoing game jam
    And I already submitted my game
    And I already filled the google form
    When I resubmit my game
    Then It should be updated
    And I should not get then url to the google form
    
Scenario: Resubmitting a game
          Google form NOT submitted
 
    Given there is an ongoing game jam
    And I already submitted my game
    When I resubmit my game
    Then it should be updated
    And I should get the url to the google form
     
    
Scenario: No submission if there is no game jam
    Given there is no ongoing game jam
    When I submit a game
    Then the submission should be rejected
    And the message schould be:
    """
    Sorry, there is no game jam at this time
    """
    
Scenario: Updating the game should always be possible
    Given there is an ongoing game jam
    And I already submitted my game
    When I upload my game
    Then it should be updated


Scenario: A game is fully submitted if and only if the the google form is filled out
    

#Scenario: No submission in judging phase
#    Given the ongoing game jam is in judging phase
#    When I submit a game
#    Then the submission should be rejected
#    And the message should be:
#    """
#    Sorry, the submission phase of this jam has ended
#    """

#Scenario: No normal uploading the game if it was submitted
#    Given there is an ongoing game jam
#    And I already submitted my game
#    When I upload my game
#    Then the upload should be rejected
#    And the message should be
#    """
#    You already submitted this game, please use submit if you want to change it
#    """



  Scenario: program upload with valid data
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file
    And I have a parameter "fileChecksum" with the md5checksum of "test.catrobat"
    When I POST these parameters to "/pocketcode/api/submit.json"
    Then I should get the json object with random "token" and "projectId":
      """
      {
	      "projectId":"",
	      "statusCode":200,
	      "answer":"Your project was uploaded successfully!",
	      "token":"","preHeaderMessages":""
	      "form": "https://...google"
      }
      """
    And the returned "projectId" should be a number

    