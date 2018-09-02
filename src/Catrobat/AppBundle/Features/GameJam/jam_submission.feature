Feature: Submitting games to a game jam


  Scenario: Submitting a game

    Given There is an ongoing game jam
    When I submit a game
    Then I should get the url to the google form
    But The game is not yet accepted


  Scenario: Accepting a game

    Given I submitted a game
    When I fill out the google form
    Then My game should be accepted


  Scenario: Resubmitting a game
  Google form submitted

    Given There is an ongoing game jam
    And I already submitted my game
    And I already filled the google form
    When I resubmit my game
    Then It should be updated
    And I should not get the url to the google form
    And My game should still be accepted


  Scenario: Resubmitting a game
  Google form NOT submitted

    Given There is an ongoing game jam
    And I already submitted my game
    But I did not fill out the google form
    When I resubmit my game
    Then It should be updated
    And I should get the url to the google form
    But The game is not yet accepted


  Scenario: No submission if there is no game jam

    Given there is no ongoing game jam
    When I submit a game
    Then The submission should be rejected
    And The message schould be:
      """
      Sorry, there is no game jam at this time
      """


  Scenario: Updating the game should always be possible

    Given There is an ongoing game jam
    And I already submitted my game
    When I upload my game
    Then It should be updated

