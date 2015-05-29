Feature: Detect programs with special bricks 

  In order to make sure users are notified if a program needs special devices
  As a site owner
  I want to be able to automatically flag programs with special bricks

  Scenario: Flag a program with phiro bricks
    
    When I upload a catrobat program with phiro bricks
    Then the program should be flagged as phiro

  Scenario: Flag a program with lego bricks
    
    When I upload a catrobat program with lego bricks
    Then the program should be flagged as lego
