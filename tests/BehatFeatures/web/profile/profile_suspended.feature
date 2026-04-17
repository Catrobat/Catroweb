@web @profile_page
Feature: Suspended user is blocked from logging in

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |

  Scenario: Suspended user cannot log in
    Given the user "Catrobat" profile is hidden
    And I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "username__input" with "Catrobat"
    And I fill in "password__input" with "123456"
    And I press "Login"
    And I wait for the page to be loaded
    Then the element "#login-alert-suspended" should be visible
    And the element "#login-alert" should not be visible

  Scenario: Non-suspended user does not see suspension alert on login
    Given I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "username__input" with "Catrobat"
    And I fill in "password__input" with "123456"
    And I press "Login"
    And I wait for the page to be loaded
    Then I should not see "has been suspended"
