@admin
Feature: Admin example programs
  All projects should be listed with their corresponding example flag

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Catrobat | 123456   | eeeeeeeeee | admin@pocketcode.org |  0 |
    And there are users:
      | id | name     |
      | 2  | User1    |
      | 3  | Catrobat2|
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |
      | 3  | project 3 | User1    |
      | 4  | project 4 | User1    |
      | 5  | project 5 | User1    |
      | 6  | project 6 | Catrobat2|
      | 7  | project 7 | Catrobat2|
    And following projects are examples:
      | name      | active | priority | imagetype |
      | project 4 | 0      | 1        | png       |
      | project 5 | 1      | 3        | png       |
      | project 6 | 1      | 2        | png       |

  Scenario: List all example programs
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/admin/example_program/list"
    And I wait for the page to be loaded
    Then I should see the example table:
      | Id | Example Image | Program   | Flavor     | Priority | iOS only | Active |
      | 1  |               | project 4 | pocketcode | 1        | 0        | 0      |
      | 2  |               | project 5 | pocketcode | 3        | 0        | 1      |
      | 3  |               | project 6 | pocketcode | 2        | 0        | 1      |
