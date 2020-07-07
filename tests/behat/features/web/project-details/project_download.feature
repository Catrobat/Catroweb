@web @project_page
Feature: As a visitor I want to be able to download projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | downloads | owned by | apk_ready |
      | 1  | project 1 | 5         | Catrobat | true      |

  Scenario: I want to download a project via the button
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element ".url-download" should be visible
    And the element ".url-download" should have a attribute "onclick" with value "program.download("

  Scenario: Clicking the download button should deactivate the download button until download is finished
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click ".url-download"
    Then the element "#download-progressbar-small" should be visible
    Then the button ".url-download" should be disabled until download is finished
