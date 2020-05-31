@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | description    | owned by | apk_ready |
      | 1  | project 1 | my description | Catrobat | true      |

  Scenario: Viewing project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    Then I should see "DESCRIPTION"
    And I should see "my description"
    Then I should see "CREDITS"
    And I should see "No credits available."
    And I should see "Download the project"
    And I should see "Show Remix Graph"
    And I should see "Download as app"
    And I should see "Catrobat"

  Scenario: Viewing the uploader's profile page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#icon-author a"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"

  Scenario: On the project page there should be all buttons visible to web and android
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Download the project"
    And I should see "Show Remix Graph"
    And I should see "Download as app"

  Scenario: On the project page there should be no apk button be visible to ios users
    Given I use an ios app
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Download the project"
    And I should see "Show Remix Graph"
    And I should not see "Download as app"
