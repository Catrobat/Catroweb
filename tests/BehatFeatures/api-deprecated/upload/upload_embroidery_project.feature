@api @upload @tag
Feature: Upload a project with tag

  Background:
    Given there are extensions:
      | id | internal_title |
      | 1  | arduino        |
      | 2  | drone          |
      | 3  | mindstorms     |
      | 4  | phiro          |
      | 5  | embroidery     |

  Scenario: uploading a embroidery project should add the extension to the project
    Given I have an embroidery project
    And I use the "english" app, API version 1
    When I upload this generated project, API version 1
    Then the embroidery project should have the "embroidery" file extension

  Scenario: uploading a normal project should not add an extension to the project
    Given I have a project
    And I use the "english" app, API version 1
    When I upload this generated project, API version 1
    Then the project should have no extension

