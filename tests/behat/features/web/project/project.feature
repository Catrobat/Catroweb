@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | description    | owned by | apk_ready |
      | 1  | project 1 | my description | Catrobat | true      |
      | 2  | project 2 | my descriptionA| Catrobat | true      |
      | 3  | project 3 | my descriptionB| Catrobat | true      |
      | 4  | project 4 | my descriptionC| Catrobat | true      |
      | 5  | project 5 | my descriptionD| Catrobat | true      |



    And following projects are featured:
      | id | project   | url                   | active | priority |
      | 2  | project 2 |                       | yes    | 1        |
      | 3  | project 3 |                       | yes    | 2        |
      | 4  | project 4 |                       | yes    | 3        |
      | 5  | project 5 |                       | yes    | 4        |

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
    And I should see "Link"
    And I should see "Report Project"
    And I should see "Catrobat"

  Scenario: Viewing the uploader's profile page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#icon-author a"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"

  Scenario: I want a link to this project
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element ".btn-copy" should be visible

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

  Scenario: Featured Programs can not be reported, while others should be
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "project 2"
    Then the element "#report-program-button" should not exist
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "project 3"
    Then the element "#report-program-button" should not exist
    Given I am on "/app/project/4"
    And I wait for the page to be loaded
    Then I should see "project 4"
    Then the element "#report-program-button" should not exist
    Given I am on "/app/project/5"
    And I wait for the page to be loaded
    Then I should see "project 5"
    Then the element "#report-program-button" should not exist
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    Then the element "#report-program-button" should exist