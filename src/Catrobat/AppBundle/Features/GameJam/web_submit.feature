@web
Feature: Submitting a game to a gamejam via web interface

Scenario:
    Given There is an ongoing game jam
      And I am logged in
     When I visit the details page of my program
     Then There should be a button to submit it to the jam
     
Scenario:
    When I submit my program to a gamejam
    Then I should be redirected to the google form
    
Scenario:
    Given I am logged in
      And I submitted a program to the gamejam
      And I filled out the google form
     When I visit the details page of my program
     Then There should not be a button to submit it to the jam

Scenario:
    Given There is an ongoing game jam
      And I am logged in
     When I visit the details page of a program from another user
     Then There should not be a button to submit it to the jam
     
Scenario:
    Given There is no ongoing game jam
     When I visit the details page of my program
     Then There should not be a button to submit it to the jam
    
