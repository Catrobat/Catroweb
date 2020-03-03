@web @security
Feature:
  The password field visibility of the login form should be changeable for the user

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: The password should be hidden as default behaviour
    Given I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then the element ".show-hide-password input" should have type "password"
    But the element ".show-hide-password input" should not have type "text"

  Scenario: It should be possible to change the visibility of the password
    Given I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then the element ".show-hide-password input" should have type "password"
    But the element ".show-hide-password input" should not have type "text"
    When I click "#password-visibility-toggler"
    And I wait for AJAX to finish
    Then the element ".show-hide-password input" should have type "text"
    But the element ".show-hide-password input" should not have type "password"
    When I click "#password-visibility-toggler"
    And I wait for AJAX to finish
    Then the element ".show-hide-password input" should have type "password"
    But the element ".show-hide-password input" should not have type "text"
