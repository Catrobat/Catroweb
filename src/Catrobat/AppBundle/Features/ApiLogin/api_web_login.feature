Feature: In order to be logged into the webview after an upload from the app
         As an app developer
         I need the possibilty to directly login with the given upload token
         
Scenario:
    Given I have a valid upload token
     When I login with this token and my username
     Then I should be logged in

Scenario: For an easy logout, specifying a wrong key
          should log the current user out of the System

    Given I am logged in
      And I have an invalid upload token
     When I login with this token and my username
     Then I should be logged out

 