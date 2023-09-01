@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Catrobat2 |
    And there are projects:
      | id | name      | description    | owned by | apk_ready |
      | 1  | project 1 | my description | Catrobat | true      |
      | 2  | project 2 | my description | Catrobat2 | true      |


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
    And I click "#program-owner-username"
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

  Scenario: Steal Project should work when logged in
    Given I log in as "Catrobat"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    When I click "#stealProjectButton"
    And I wait for the page to be loaded
    Then the element "#stealProjectButton" should not exist

  Scenario: Steal Project should not work when not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#stealProjectButton" should not exist

  Scenario: Steal Project should not work when you are the Project Owner
    Given I log in as "Catrobat2"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#stealProjectButton" should not exist