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
    And I should see "If an account matching your email exists, then an email was just sent that contains a link that you can use to reset your password. This link will expire in 1 hour."
