@web @security
Feature:
  As a user, I want to login using OAuth services

  Background:
    Given there are users:
      | id | name       | oauth_user |
      | 1  | Catrobat   | true       |
      | 2  | TestUser   | false      |

  Scenario: When I login for the first time using OAuth service I should see OAuth Info popup
    Given I  log in as "Catrobat"
    Then I should see "External account sign in information"
    And I logout
    And  I  log in as "Catrobat"
    Then I should not see "External account sign in information"
    Given I log in as "TestUser"
    Then I should not see "External account sign in information"

  Scenario: When I click link in the Oauth Info popup I should be redirected on my profile
    Given I log in as "Catrobat"
    Then I should see "External account sign in information"
    And I click on the "here" link
    Then I should be on "/pocketcode/user"

  Scenario: OAuth users should be able to create a new password
    Given I log in as "Catrobat"
    And I am on "/app/user"
    Then I should see "Create new password"
    Then I click "#edit-password-button"
    And I fill in "password" with "test12"
    And I fill in "repeat-password" with "test12"
    And I click "#save-password"
    And I wait for AJAX to finish
    Then I should see "Success"
    And I click ".swal2-confirm"
    Then I should see "Password"

  Scenario: I should see google, facebook, and apple log in buttons on the login page and on the registration page
    Given I am on "app/login"
    Then the element "#btn-login-google" should be visible
    And the element "#btn-login-facebook" should be visible
    And the element "#btn-login-apple" should be visible
    And I click "#btn-login-google"
    Then the element "#termsModal" should be visible
    Given I am on "app/register"
    Then the element "#btn-login-google" should be visible
    And the element "#btn-login-facebook" should be visible
    And the element "#btn-login-apple" should be visible
    And I click "#btn-login-facebook"
    Then the element "#termsModal" should be visible