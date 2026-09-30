@web @project_page
Feature: Minor users cannot open not-for-kids projects, except their own

  Background:
    Given there are users:
      | id | name       | is_minor | consent_status | parent_email   |
      | 1  | MinorOwner | true     | granted        | parent@test.at |
      | 2  | OtherMinor | true     | granted        | parent@test.at |
    And there are projects:
      | id | name      | owned by   | not_for_kids |
      | 1  | project 1 | MinorOwner | 1            |

  Scenario: Minor owner can still open their own not-for-kids project
    Given I log in as "MinorOwner"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1"
    And the element "#top-app-bar__btn-toggle-not-for-kids" should exist

  Scenario: Other minor is redirected away from a not-for-kids project
    Given I log in as "OtherMinor"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should be on "/app/"
