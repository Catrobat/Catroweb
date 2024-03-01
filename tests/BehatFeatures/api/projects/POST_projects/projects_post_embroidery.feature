@api @projects @post @tag
Feature: Upload a program with tag

  Background:
    Given there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | raspberry_pi   |
      | 6  | embroidery     |

  Scenario: uploading a embroidery program should add the embroidery extension to the program
    Given I have an embroidery project
    And I use the "english" app, API version 2
    When I upload this generated project, API version 2
    Then the embroidery project should have the "embroidery" file extension

  Scenario: uploading a normal program should must not add an extension to the program
    Given I have a project
    And I use the "english" app, API version 2
    When I upload this generated project, API version 2
    Then the project should have no extension
