Feature: To avoid accidental losing program files due to overriding on limited accounts
  A Snapshot will be created on every update.

  @disabled
  Scenario:
    Given I am logged in
    And I have a limited account
    And the next Uuid Value will be "1"
    When I update my program
    Then A copy of this program will be stored on the server
     