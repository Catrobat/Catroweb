@web  @disabled
Feature: Submitting a game to a gamejam via web interface

  Scenario:
    Given There is an ongoing game jam
    And I am logged in
    When I visit the details page of my program
    Then There should be a button to submit it to the jam

  Scenario:
    Given I submit my program to a gamejam
    And I should be redirected to the google form
    When I visit the details page of my program
    Then There should be a button to submit it to the jam

  Scenario:
    Given I am logged in
    And I submitted a program to the gamejam
    And I filled out the google form
    When I visit the details page of my program
    Then There should not be a button to submit it to the jam

  Scenario:
    Given There is no ongoing game jam
    When I visit the details page of my program
    Then There should not be a button to submit it to the jam
    And There should not be a div with whats the gamejam

  Scenario:
    Given There is an ongoing game jam
    And I am not logged in
    When I visit the details page of a program from another user
    Then There should be a button to submit it to the jam
    And There should be a div with whats the gamejam

  Scenario:
    Given There is an ongoing game jam
    And I am logged in
    When I visit the details page of a program from another user
    Then There should be a button to submit it to the jam

  Scenario:
    Given There is an ongoing game jam
    And I am not logged in
    And I visit the details page of my program
    When I submit the program
    And I login
    Then I should be on the details page of my program

  Scenario:
    Given There is an ongoing game jam
    And I am logged in
    And I visit the details page of a program from another user
    When I submit the program
    Then I should see the message "You can only submit your own programs"

  Scenario: There shouldn't be a submit button when the current gamejam has no flavor set
    Given There is an ongoing game jam without flavor
    And I am logged in
    When I visit the details page of my program
    Then There should not be a button to submit it to the jam

      