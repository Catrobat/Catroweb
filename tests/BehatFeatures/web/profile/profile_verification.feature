@web @profile_page
Feature: As a visitor I want to see if a user is verified

  Background:
    Given there are users:
      | id | name      | verified |
      | 1  | Catrobat  | true     |
      | 2  | OtherUser | false    |

  Scenario: I should see my own verification status
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then the element ".verification-icon" should be visible

  Scenario: I should see the verification status of any user
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then the element ".verification-icon" should be visible

  Scenario: I should see my own verification status (neg)
    Given I log in as "OtherUser"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then the element ".verification-icon" should not exist

  Scenario: I should see the verification status of any user (neg)
    And I am on "/app/user/2"
    And I wait for the page to be loaded
    Then the element ".verification-icon" should not exist

  Scenario: I can't request an email if I am already verified
    Given I log in as "Catrobat"
    And I am on "/app/user"
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    Then I should not see "Verify Account"

  Scenario: I should be able to request the email to verify my account
    Given I log in as "OtherUser"
    And I am on "/app/user"
    Given I click "#top-app-bar__btn-settings"
    And I wait for the element "#user-settings-modal" to be visible
    Then I should see "Verify Account"
    And I click ".profile__user-settings .nav-link[data-bs-target='#verify-settings-modal']"
    And I wait for the element "#verify-settings-modal" to be visible
    Then I should see "Verify account"
    And the element "#btn-verify-account" should be visible
    When I click "#btn-verify-account"
    Then the element "#btn-verify-account" should be disabled
