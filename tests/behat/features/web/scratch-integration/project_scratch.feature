@project_page

Feature: As a visitor I want to see scratch projects

  Scenario: Visit a Scratch program
    Given I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat program
    And there should be "1" programs in the database
    And there should be "1" users in the database

  Scenario: Visiting a not existing Scratch program
    Given I am on "/app/scratch/project/1"
    Then the response status code should be 404
    And there should be "0" programs in the database
    And there should be "0" users in the database

  Scenario: Visiting a Scratch program twice
    Given I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat program
    And  I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat program
    And there should be "1" programs in the database
    And there should be "1" users in the database

  Scenario: Visiting two Scratch programs made by the same user
    Given I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat program
    And I request "GET" "/app/scratch/project/398466654"
    Then I should be redirected to a catrobat program
    And there should be "2" programs in the database
    And there should be "1" users in the database