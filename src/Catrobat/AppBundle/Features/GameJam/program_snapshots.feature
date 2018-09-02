Feature: To avoid accidental losing program files due to overriding on limited accounts
  A Snapshot will be created on every update.


  Scenario:
    Given I have a limited account
    When I update my program
    Then A copy of this program will be stored on the server
     