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
    Then I should see "Description"
    And I should see "my description"
    Then I should see "Notes and credits"
    And I should see "No notes and credits available."
    And I should see "Download"
    And I should see "Remix Graph"
#    And I should see "Download as app"
    And I should see "Catrobat"
    And I should see "Statistics"
    And I should see "Code View"

  Scenario: Viewing the uploader's profile page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#project-owner-username"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"

  Scenario: On the project page there should be all buttons visible to web and android
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Download"
    And I should see "Remix Graph"
#    And I should see "Download as app"
    And I should see "Statistics"
    And I should see "Code View"

  Scenario: On the project page there should be no apk button be visible to ios users
    Given I use an ios app
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Download"
    And I should see "Remix Graph"
    And I should not see "Download as app"
    And I should see "Statistics"
    And I should see "Code View"
