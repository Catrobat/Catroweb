@web @security
Feature:
  As a user, I want to login using OAuth services

  Background:
    Given there are users:
      | id | name     | oauth_user |
      | 1  | Catrobat | true       |
      | 2  | TestUser | false      |

  Scenario: When I login for the first time using OAuth service I should see OAuth Info popup
    Given I log in as "Catrobat"
    And I wait for the page to be loaded
    Then I should see "External account sign in information"
    And I click ".swal2-confirm"
    And I logout
    And  I log in as "Catrobat"
    Then I should not see "External account sign in information"
    Given I log in as "TestUser"
    Then I should not see "External account sign in information"

  @disabled
  Scenario: OAuth users should be able to create a new password (Attention: flaky test!)
    Given I log in as "Catrobat"
    And I am on "/app/user"
    Then I should see "Create new password"
    Then I click "#edit-password-button"
    And I fill in "_password" with "test12"
    And I fill in "repeat-password" with "test12"
    And I click "#save-password"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    Then I should see "Success"
    And I click ".swal2-confirm"
    Then I should see "Password"

  Scenario: I should see google log in button on the login page and on the registration page
    Given I am on "app/login"
    Then the element "#btn-login-google" should be visible
    Given I am on "app/register"
    Then the element "#btn-login-google" should be visible

  Scenario: New user registers via Google OAuth, completes date of birth, and sees the random username info popup
    Given I am on "/app/login"
    And I wait for the page to be loaded
    When I click "#btn-login-google"
    And I wait for the page to be loaded
    Then I should be on "/dev/fake-oauth"
    When I fill in "email" with "new-oauth-user@catrobat.org"
    And I fill in "first_name" with "New"
    And I fill in "last_name" with "OAuthUser"
    And I click "#btn-fake-oauth-submit"
    And I wait for the page to be loaded
    Then I should be on "/complete-registration"
    When I fill in "date-of-birth__input" with "2000-06-15"
    And I click "#complete-registration-form button[type=submit]"
    And I wait for the page to be loaded
    Then I should be on "/"
    And I should see "External account sign in information"
    And I should see "random username"
    When I click ".swal2-confirm"
    Then I should not see "External account sign in information"
