@project @extensions
Feature: Projects extensions must be added automatically

  Scenario: Projects with extensions should keep their extensions as long as the extensions do exist
    Given I have a project with arduino, mindstorms and phiro extensions
    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    And I upload this generated project with id "1"
    Then the project with id "1" should be marked with "3" extensions in the database
    When I run the refresh project extensions command
    Then the project with id "1" should be marked with "3" extensions in the database

  Scenario: Projects with extensions should keep their extensions as long as the extensions do exist
    Given I have a project with arduino, mindstorms and phiro extensions
    And I upload this generated project with id "1"
    Then the project with id "1" should be marked with "0" extensions in the database
    And there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    When I run the refresh project extensions command
    And I wait 3000 milliseconds
    Then the project with id "1" should be marked with "3" extensions in the database

  Scenario: Projects that should not have their extensions, must be stripped lose their extensions
    Given there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
    And there are projects:
      | id | extensions               |
      | 1  | arduino,phiro,mindstorms |
    Then the project with id "1" should be marked with "3" extensions in the database
    When I run the refresh project extensions command
    And I wait 3000 milliseconds
    Then the project with id "1" should be marked with "0" extensions in the database