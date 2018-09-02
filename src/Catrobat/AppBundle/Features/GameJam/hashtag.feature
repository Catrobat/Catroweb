@web
Feature: Checking hashtag in description

  Scenario:
    Given I am logged in
    And There is an ongoing game jam with the hashtag "#whatever"
    And I submit a program to this gamejam
    And I filled out the google form
    When I visit the details page of my program
    Then I should see the hashtag "#whatever" in the program description


    
