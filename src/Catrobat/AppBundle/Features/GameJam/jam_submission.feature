Feature: Submitting games to a game jam

Scenario: Submitting a game
    Given there is an ongoing game jam
    When I submit a game
    Then it should be accepted
    And I should get the url to my game
    
Scenario: Resubmitting a game
    Given there is an ongoing game jam
    And I already submitted my game
    When I resubmit my game
    Then it should be updated

Scenario: No submission if there is no game jam
    Given there is no ongoing game jam
    When I submit a game
    Then the submission should be rejected
    And the message schould be:
    """
    Sorry, there is no game jam at this time
    """
    
Scenario: No submission in judging phase
    Given the ongoing game jam is in judging phase
    When I submit a game
    Then the submission should be rejected
    And the message should be:
    """
    Sorry, the submission phase of this jam has ended
    """

Scenario: No normal uploading the game if it was submitted
    Given there is an ongoing game jam
    And I already submitted my game
    When I upload my game
    Then the upload should be rejected
    And the message should be
    """
    You already submitted this game, please use submit if you want to change it
    """
