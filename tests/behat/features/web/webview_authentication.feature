@homepage
Feature: Users should be logged in automatically when they are logged in in the app

  Background:
    Given there are users:
      | name     | password | token                            | email          | id |
      | Catrobat | 123456   | cafe000000deadbeef1111affe227357 | dev1@catrob.at | 1  |
      | User2    | 654321   | 112233445566778899aabbccddeeff00 | dev2@catrob.at | 2  |
      | User3    | 654321   |                                  | dev3@catrob.at | 3  |
      | User4    | 654321   | 99990bad099990bad099990bad099999 | dev4@catrob.at | 4  |

  Scenario: Log in using Catrobat user and show profile
    Given I set the cookie "CATRO_LOGIN_USER" to "Catrobat"
    And I set the cookie "CATRO_LOGIN_TOKEN" to "cafe000000deadbeef1111affe227357"
    And I am on "/app/user"
    Then I should see "My Profile"
    Then I should see "dev1@catrob.at"

  Scenario: Log in using Catrobat user with wrong token
    Given I set the cookie "CATRO_LOGIN_USER" to "Catrobat"
    And I set the cookie "CATRO_LOGIN_TOKEN" to "deadbeef"
    And I am on "/app/user"
    Then I should not see "dev1@catrob.at"
    And I should see "Your user credentials are wrong"

  Scenario: Log in using unknown user
    Given I set the cookie "CATRO_LOGIN_USER" to "Badguy"
    And I set the cookie "CATRO_LOGIN_TOKEN" to "deadbeef"
    And I am on "/app/user"
    Then I should not see "My Profile"
    And I should see "Your user credentials are wrong"

  Scenario: Log in using empty token should be ignored
    Given I set the cookie "CATRO_LOGIN_USER" to "User3"
    And I set the cookie "CATRO_LOGIN_TOKEN" to ""
    And I am on "/app/user"
    Then I should not see "My Profile"
    And I should be on "/app/login"
    And I should see 1 "input#password"

  Scenario: Log in without user cookie should be ignored
    And I set the cookie "CATRO_LOGIN_TOKEN" to "99990bad099990bad099990bad099999"
    And I am on "/app/user"
    Then I should not see "My Profile"
    And I should be on "/app/login"
    And I should see 1 "input#password"

  Scenario: Logout button should be hidden
    Given I set the cookie "CATRO_LOGIN_USER" to "User2"
    And I set the cookie "CATRO_LOGIN_TOKEN" to "112233445566778899aabbccddeeff00"
    And I am on the homepage
    And I open the menu
    Then I should see 0 "#btn-logout"
    Then I should see 0 "#btn-login"
    Then I should see 1 "#btn-profile"

  Scenario: Logout should not be possible
    Given I set the cookie "CATRO_LOGIN_USER" to "User2"
    And I set the cookie "CATRO_LOGIN_TOKEN" to "112233445566778899aabbccddeeff00"
    And I am on the homepage
    And I go to "/app/logout"
    And I open the menu
    Then I should see 1 "#btn-profile"
    And I go to "/app/user"
    Then I should see "My Profile"
    Then I should see "dev2@catrob.at"
