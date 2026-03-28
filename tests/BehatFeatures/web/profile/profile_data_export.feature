@web @profile_page
Feature:
  As a logged in user
  I want to see a data export button in my account settings
  So that I can download a copy of my personal data

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | upload time      |
      | 1  | project 1 | Catrobat | 01.01.2013 12:00 |

  Scenario: Logged in user sees the data export button in account settings
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#account-settings-modal']"
    And I wait for the element "#account-settings-modal" to be visible
    Then the element "#btn-export-data" should be visible
    And I should see "Download my data"
    And I should see "You have the right to receive a copy of your personal data."

  Scenario: Logged in user also sees delete account button alongside data export
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    When I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    And I click ".profile__user-settings .nav-link[data-bs-target='#account-settings-modal']"
    And I wait for the element "#account-settings-modal" to be visible
    Then the element "#btn-export-data" should be visible
    And the element "#btn-delete-account" should be visible

  Scenario: Guest user cannot access the profile page
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "Download my data"
