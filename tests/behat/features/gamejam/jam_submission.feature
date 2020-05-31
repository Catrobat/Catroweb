Feature: Submitting games to a game jam


  Scenario: Submitting a game

    Given I am logged in
    And There is an ongoing game jam
    When I submit a game with id "1"
    Then I should get the url to the google form
    But The game is not yet accepted


  Scenario: Accepting a game

    Given I am logged in
    And There is an ongoing game jam
    And I submitted a game with id "1"
    When I fill out the google form
    Then My game should be accepted


  Scenario: Resubmitting a game
  Google form submitted

    Given I am logged in
    And  There is an ongoing game jam
    And I already submitted my game with id "1"
    And I already filled the google form with id "1"
    When I resubmit my game
    Then it should be updated, API version 1
    And I should not get the url to the google form
    And My game should still be accepted


  Scenario: Resubmitting a game
  Google form NOT submitted

    Given I am logged in
    And  There is an ongoing game jam
    And I already submitted my game with id "1"
    But I did not fill out the google form
    When I resubmit my game
    Then it should be updated, API version 1
    And I should get the url to the google form
    But The game is not yet accepted


  Scenario: No submission if there is no game jam

    Given There is no ongoing game jam
    When I submit a game with id "1"
    Then The submission should be rejected
    And The message should be:
      """
      Sorry, there is no game jam at this time
      """


  Scenario: Updating the game should always be possible

    Given There is an ongoing game jam
    And I already submitted my game with id "1"
    When I upload my game
    Then it should be updated, API version 1

