@web @project_page
Feature: As a visitor I need to login to be able to download projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | downloads | owned by | apk_ready |
      | 1  | project 1 | 5         | Catrobat | true      |
      | 2  | project 2 | 5         | Catrobat | true      |

Scenario: I want to download a project via the button
    When I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectDownloadDisabledButton-small" should be visible
    And I click "#projectDownloadDisabledButton-small"
    Then I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    Then I press "Login"
    And I wait for the page to be loaded
    Then I should be logged in
    And I am on "/app/project/1"