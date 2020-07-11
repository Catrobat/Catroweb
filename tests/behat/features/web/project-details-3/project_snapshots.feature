Feature: To avoid accidental losing program files due to overriding on limited accounts
  A Snapshot will be created on every update.

  Background:
    Given there are admins:
      | name     | password | id |
      | Admin    | 123456   |  1 |

  Scenario: The snapshot system should create a snapshot of the project file on every upload
    Given I have a program
    And the current time is "11.07.2020 16:00"
    And I upload the program with the id "1", API version 2
    And I enable snapshots for the project with id "1"
    And the current time is "12.08.2020 18:00"
    When I update this program
    And I disable snapshots for the project with id "1"
    And the current time is "13.09.2020 20:00"
    When I update this program
    Then 1 copies of this program will be stored on the server
    When I enable snapshots for the project with id "1"
    And the current time is "15.08.2020 16:30"
    And I update this program
    Then 2 copies of this program will be stored on the server
    Given I log in as "Admin"
    And I am on "/admin/snapshots/list"
    And I wait for the page to be loaded
    Then I should see "Filename"
    Then I should see "Size"
    Then I should not see "1__2020-07-11_16-00-00.catrobat"
    Then I should see "1__2020-08-12_18-00-00.catrobat"
    Then I should not see "1__2020-09-13_20-00-00.catrobat"
    Then I should see "1__2020-08-15_16-30-00.catrobat"
