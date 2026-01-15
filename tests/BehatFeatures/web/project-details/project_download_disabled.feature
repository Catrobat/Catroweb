@web @project_page
Feature: As a visitor I can download projects without login, but downloads are only counted for logged-in users

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | downloads | owned by | apk_ready |
      | 1  | project 1 | 5         | Catrobat | true      |
      | 2  | project 2 | 5         | Catrobat | true      |

  Scenario: I can download a project without being logged in
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectDownloadButton-small" should be visible
    And the element "#projectDownloadDisabledButton-small" should not exist