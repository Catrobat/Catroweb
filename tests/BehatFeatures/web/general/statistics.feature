Feature: We keep statistic about the usage of the platform, while deleting inactive users/projects

  Background:
    Given there are statistics with "0" user and "0" projects

  Scenario: Registering as a new user should increment the user count
    Given I am a valid user
    And There should be statistics with "1" user and "0" projects

  Scenario: Uploading a new project should increase the projects count
    Given I have a project with "name" set to "First program"
    When I upload a generated project, API version 2
    Then the uploaded project should exist in the database, API version 2
    And There should be statistics with "1" user and "1" projects

  Scenario: Statistics are shown in the footer
    Given there are statistics with "10" user and "17" projects
    When I go to "/"
    Then the ".footer-download" element should contain "10"
    Then the ".footer-download" element should contain "17"


