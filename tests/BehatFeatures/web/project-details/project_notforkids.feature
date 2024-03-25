@web @project_page
Feature: As a project owner I want to be able to mark my projects as not for kids

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
    And there are projects:
      | id | name      | downloads | owned by | apk_ready | not_for_kids |
      | 1  | project 1 | 5         | Catrobat | true      | 0            |
      | 2  | project 2 | 5         | User     | true      | 1            |
      | 3  | project 3 | 5         | User     | true      | 2            |
      | 4  | project 4 | 5         | User     | true      | 0            |


  Scenario: I do not want to see the mark not safe for kids button when I am not the owner of the project
    Given I log in as "Catrobat"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    Then the element "#projectNotForKidsButton" should not exist

  Scenario: I want to see the mark not safe for kids button when I am the owner of the project
    Given I log in as "User"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    Then the element "#projectNotForKidsButton" should exist
    Then the element "#markNotForKidsText" should exist

  Scenario: I want to see the mark safe for kids text when I am the owner of the project and the project is already marked as not for kids
    Given I log in as "User"
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#projectNotForKidsButton" should exist
    And the element "#markSafeForKidsText" should exist

  Scenario: I want to be able to mark my project as not safe for kids
    Given I log in as "User"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    When I click "#projectNotForKidsButton"
    And I wait for the page to be loaded
    Then I should see "Not for kids"