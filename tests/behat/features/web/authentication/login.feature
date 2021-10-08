@web @security
Feature:
  As a user, I want to login or request my password

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: Login and logout
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    Then I press "Login"
    And I wait for the page to be loaded
    Then I should be logged in
    And I should see "Catrobat"
    When I am on "/app/logout"
    And I wait for the page to be loaded
    Then I should be logged out

  Scenario: Try to login with wrong username should fail
    Given I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "abcdefg"
    And I fill in "_password" with "123456"
    And I press "Login"
    And I wait for the page to be loaded
    Then I should see "Your password or username was incorrect."

  Scenario: Request password should work for an existing user and just once in 24 hours
    Given I am on "/app/login"
    And I wait for the page to be loaded
    When I click "#pw-request"
    And I wait for the page to be loaded
    Then I should be on "/app/resetting/request"
    And I wait for the page to be loaded
    When I fill in "username" with "abcd"
    And I press "recover"
    Then I wait for the page to be loaded
    And I should see "Your username or email address was not found."
    When I fill in "username" with "Catrobat"
    And I press "recover"
    Then I wait for the page to be loaded
    And I should see "An email was sent to your email address. Please check your inbox."
    When I go to "/app/resetting/request"
    And I wait for the page to be loaded
    And I fill in "username" with "Catrobat"
    And I press "recover"
    Then I wait for the page to be loaded
    And I should see "The password for this user has already been requested within the last 24 hours."

  Scenario: The referer should work even after one failed login
    Given I am on homepage
    And I wait for the page to be loaded
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    And I wait for the page to be loaded
    Then I should be on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123"
    Then I press "Login"
    And I wait for the page to be loaded
    Then I should see "Your password or username was incorrect."
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    Then I press "Login"
    And I wait for the page to be loaded

  Scenario: When visiting the page directly to the login page, after login i should be on the index page
    Given  there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    And I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    When I press "Login"
    And I wait for the page to be loaded
    Then I should see "Newest"

  Scenario: The password should be hidden as default behaviour
    Given I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    Then the element "#password__input" should have type "password"
    But the element "#password__input" should not have type "text"
    And the ".password-toggle" element should contain "visibility"

  Scenario: It should be possible to change the visibility of the password
    Given I am on "/app/login"
    And I wait for the page to be loaded
    And I fill in "_username" with "Catrobat"
    And I fill in "_password" with "123456"
    Then the element "#password__input" should have type "password"
    And the ".password-toggle" element should contain "visibility"
    But the element "#password__input" should not have type "text"
    When I click ".password-toggle"
    Then the element "#password__input" should have type "text"
    And the ".password-toggle" element should contain "visibility_off"
    But the element "#password__input" should not have type "password"
    When I click ".password-toggle"
    Then the element "#password__input" should have type "password"
    And the ".password-toggle" element should contain "visibility"
    But the element "#password__input" should not have type "text"
