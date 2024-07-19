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

  Scenario: I should see google, facebook, and apple log in buttons on the login page and on the registration page
    Given I am on "app/login"
    Then the element "#btn-login-google" should be visible
    And the element "#btn-login-facebook" should be visible
    And the element "#btn-login-apple" should be visible
    Given I am on "app/register"
    Then the element "#btn-login-google" should be visible
    And the element "#btn-login-facebook" should be visible
    And the element "#btn-login-apple" should be visible
