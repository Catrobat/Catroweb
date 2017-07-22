@homepage
  Feature:
    As a user, I want to login or request my password

  Background:
    Given there are users:
      | name     | password | token       | email               |
      | Catrobat | 123456    | cccccccccc | dev1@pocketcode.org |
      | User1    | 654321    | cccccccccc | dev2@pocketcode.org |

  Scenario: Login and logout
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click "#btn-login"
    Then I should be on "/pocketcode/login"
    And I should see an "#header-logo" element
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then I press "Login"
    Then I should be logged in
    And I should see "Catrobat"
    When I am on "/logout"
    Then I should be logged out

  Scenario: Try to login with wrong username should fail
    Given I am on "/pocketcode/login"
    And I fill in "username" with "abcdefg"
    And I fill in "password" with "123456"
    And I press "Login"
    Then I should see "Your password or username was incorrect."

  Scenario: Request password should work for an existing user and just once in 24 hours
    Given I am on "/pocketcode/login"
    Then I should see "Forgot password or username?"
    When I click "#pw-request"
    Then I should be on "/pocketcode/resetting/request"
    When I fill in "username" with "abcd"
    And I press "recover"
    Then I should see "Your username or email address was not found."
    When I fill in "username" with "Catrobat"
    And I press "recover"
    Then I should see "An email was sent to your email address. Please check your inbox."
    When I go to "/pocketcode/resetting/request"
    And I fill in "username" with "Catrobat"
    And I press "recover"
    Then I should see "The password for this user has already been requested within the last 24 hours."

Scenario: The referer should work even after one failed login
  Given I am on "/pocketcode/help"
  Then I should see an "#btn-login" element
  When I click "#btn-login"
  Then I should be on "/pocketcode/login"
  And I should see an "#header-logo" element
  And I fill in "username" with "Catrobat"
  And I fill in "password" with "123"
  Then I press "Login"
  Then I should see "Your password or username was incorrect."
  And I fill in "username" with "Catrobat"
  And I fill in "password" with "123456"
  Then I press "Login"
  And I should see a big help image "Game Design"
  
Scenario: When visiting the page directly to the login page, after login i should be on the index page
  Given I am on "/pocketcode/login"
  And I fill in "username" with "Catrobat"
  And I fill in "password" with "123456"
  When I press "Login"
  Then I should see "Newest"
