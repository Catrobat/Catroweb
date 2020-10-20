@web @project_page
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | id | name     | password | salt      | email               |
      |  1 | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      |  2 | arturo   | 123456   | cccccRRccc | dev3@pocketcode.org |
      |  3 | fredy    | 123456   | ccccwwgccc | dev2@pocketcode.org |
    And there are projects:
      | id | name      | description    | owned by | apk_ready |
      | 1  | project 1 | my description | fredy | true      |
      | 2  | project 2 | my description | arturo | true      |

  Scenario: Viewing project page
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    Then I should see "Description"
    And I should see "my description"
    Then I should see "Credits"
    And I should see "No credits available."
    And I should see "Download"
    And I should see "Remix Graph"
    And I should see "Download as app"
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
    And I should see "Download as app"
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

  Scenario: Clicking in the steal project button should changing the name of the project owner to the logged in user
    Given I log in as "arturo" with the password "123456"
    And I should be logged in
    And I am logged in as normal user
    And I am on "/pocketcode/project/1"
    And I wait for the page to be loaded
    And I should see "fredy"
    When I click on the button named "Steal Project"
    And I wait for AJAX to finish
    Then I should see "arturo"
