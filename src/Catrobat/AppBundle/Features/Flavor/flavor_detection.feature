Feature: Flag programs based on the app flavor

  In order provide an index of different flavored programs
  As a site owner
  I want to be able to automatically flag programs by their flavor

  Scenario: Flag a program uploaded with the phiropro app
    
    When I upload a catrobat program with the phiropro app
    Then the program should be flagged as phiropro

  Scenario: Do not flag a program as phiropro if uploaded with pocketcode
    
    When I upload a standard catrobat program
    Then the program should not be flagged as phiropro
    