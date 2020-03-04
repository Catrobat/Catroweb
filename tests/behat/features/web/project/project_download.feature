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
    Then the link of "download" should open "download"

  Scenario: Clicking the download button should deactivate the download button for 5 seconds
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#url-download"
    And I wait for AJAX to finish
    Then the href with id "url-download" should be void
    And I wait 5000 milliseconds
    Then the href with id "url-download" should not be void
