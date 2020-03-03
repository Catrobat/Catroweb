@web @security
Feature:
  As a user, I want to login or request my password

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: Request password should work for an existing user and just once in 24 hours
    Given I am on "/app/login"
    And I wait for the page to be loaded
    When I click "#pw-request"
    And I wait for the page to be loaded
    Then I should be on "/app/resetting/request"
    And I wait for the page to be loaded
    When I fill in "username" with "abcd"
    And I press "recover"
    And I wait for AJAX to finish
    Then I should see "Your username or email address was not found."
    When I fill in "username" with "Catrobat"
    And I press "recover"
    And I wait for AJAX to finish
    Then I should see "An email was sent to your email address. Please check your inbox."
    When I go to "/app/resetting/request"
    And I wait for the page to be loaded
    And I fill in "username" with "Catrobat"
    And I press "recover"
    And I wait for AJAX to finish
    Then I should see "The password for this user has already been requested within the last 24 hours."
