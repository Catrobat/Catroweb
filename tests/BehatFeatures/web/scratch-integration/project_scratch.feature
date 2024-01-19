@project_page

Feature: As a visitor I want to see scratch projects

  Scenario: Visit a Scratch program
    Given I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat project
    And there should be "1" projects in the database
    And there should be "1" users in the database

  Scenario: Visiting a not existing Scratch program
    Given I am on "/app/scratch/project/1"
    Then I should be on "/app/"
    And there should be "0" projects in the database
    And there should be "0" users in the database

  Scenario: Visiting a Scratch program twice
    Given I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat project
    And  I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat project
    And there should be "1" projects in the database
    And there should be "1" users in the database

  Scenario: Visiting two Scratch programs made by the same user
    Given I request "GET" "/app/scratch/project/401650420"
    Then I should be redirected to a catrobat project
    And I request "GET" "/app/scratch/project/398466654"
    Then I should be redirected to a catrobat project
    And there should be "2" projects in the database
    And there should be "1" users in the database