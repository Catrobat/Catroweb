Feature: Flag projects based on the app flavor

  In order provide an index of different flavored projects
  As a site owner
  I want to be able to automatically flag projects by their flavor

  Scenario: Flag a project uploaded with the phiro app

    When I upload a catrobat project with the phiro app
    Then the project should be flagged as phiro

  Scenario: Do not flag a project as phiro if uploaded with pocketcode

    When I upload a standard catrobat project
    Then the project should not be flagged as phiro
