Feature: Detect programs with kodey bricks 

  In order provide an index of kodey programs
  As a site owner
  I want to be able to automatically flag programs with kodey bricks

  Scenario: Flag a program with kodey bricks
    
    When I upload a catrobat program with kodey bricks
    Then the program should be flagged as kodey

  Scenario: Do not flag a program as kodey if it does not contain kodey bricks
    
    When I upload a standard catrobat program
    Then the program should not be flagged as kodey
    