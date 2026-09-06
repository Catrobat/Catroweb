@web @security
Feature:
  As a user, I want to login or request my password

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: Request password should work
    Given I am on "/app/login"
    And I wait for the page to be loaded
    When I click "#pw-request"
    And I wait for the page to be loaded
    Then I should be on "/app/reset-password"
    And I wait for the page to be loaded
    When I fill in "email" with "abcd@gmail.com"
    And I press "Send reset email"
    Then I wait for the page to be loaded
    And I should see "If an account matching your email exists"

  Scenario: Reset link should show the new password form and change the password
    When I open the password reset link for "Catrobat"
    And I wait for the page to be loaded
    Then I should be on "/app/reset-password/reset"
    And I should see "Reset your password"
    When I fill in "change_password_form[plainPassword][first]" with "MyNewPassword123"
    And I fill in "change_password_form[plainPassword][second]" with "MyNewPassword123"
    And I press "Reset password"
    And I wait for the page to be loaded
    Then I should be on "/app/"
    When I log in as "Catrobat" with the password "MyNewPassword123"
    Then I should be logged in
