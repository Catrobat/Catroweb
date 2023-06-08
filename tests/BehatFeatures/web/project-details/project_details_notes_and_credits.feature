@web @project_page
Feature: As a visitor I want to the correct display of notes and credits on a projects detail page

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
    And there are projects:
      | id | name      | downloads | owned by | views | apk_ready | upload time      | credit            | scratch_id |
      | 1  | project 1 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 |                   |            |
      | 2  | project 2 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 | These are credits |            |
      | 3  | project 3 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 | These are credits | 826633052  |
      | 4  | project 4 | 5         | Catrobat | 42    | true      | 01.01.2013 12:00 |                   | 833098332  |
    And I start a new session

  Scenario: Showing no credits on project page when the project has nocredits
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "No notes and credits available."

  Scenario: Showing credits on project page when the project has credits
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then I should see "These are credits"
    And I should not see "No notes and credits available."

  Scenario: Showing credits and scratch project notice on project page when the project has credits and is a scratch project
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then I should see "These are credits"
    And I should see "This project was imported from MIT Scratch. You can check out the original project"
    And I should not see "No notes and credits available."

  Scenario: Showing scratch notice on project page when the project is a scratch project and has not credits
    Given I am on "/app/project/4"
    And I wait for the page to be loaded
    Then I should see "This project was imported from MIT Scratch. You can check out the original project"
    And I should not see "These are credits"
    And I should not see "No notes and credits available."
