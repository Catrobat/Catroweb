@web @security @deprecated
Feature: Users should be logged in automatically when they are logged in in the app

  Background:
    Given there are users:
      | name     | password | token                            | email          | id |
      | Catrobat | 123456   | cafe000000deadbeef1111affe227357 | dev1@catrob.at | 1  |
      | User2    | 654321   | 112233445566778899aabbccddeeff00 | dev2@catrob.at | 2  |
      | User3    | 654321   |                                  | dev3@catrob.at | 3  |
      | User4    | 654321   | 99990bad099990bad099990bad099999 | dev4@catrob.at | 4  |

  Scenario: Log in using Catrobat user and show profile
    Given I set the cookie "CATRO_LOGIN_TOKEN" to "cafe000000deadbeef1111affe227357"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then the "#top-app-bar__title" element should contain "My Profile"
    And the ".profile__basic-info__text__name" element should contain "Catrobat"
    And the "profile-email__input" field should contain "dev1@catrob.at"

  Scenario: Log in using Catrobat user with wrong token
    Given I set the cookie "CATRO_LOGIN_TOKEN" to "deadbeef"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "My Profile"
    And I should be on "/app/login"

  Scenario: Log in using empty token should be ignored
    Given I set the cookie "CATRO_LOGIN_TOKEN" to ""
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "My Profile"
    And I should be on "/app/login"
    And I should see 1 "#password__input"

  Scenario: Log in without user cookie should be ignored
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "My Profile"
    And I should be on "/app/login"
    And I should see 1 "#password__input"

  Scenario: Logout button should be hidden in webview
    Given I set the cookie "CATRO_LOGIN_TOKEN" to "112233445566778899aabbccddeeff00"
    And I use a release build of the Catroid app
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    Then I should see 0 "#btn-logout"
    Then I should see 0 "#btn-login"
    Then I should see 1 "#btn-profile"

  Scenario: Logout should not be possible
    Given I set the cookie "CATRO_LOGIN_TOKEN" to "112233445566778899aabbccddeeff00"
    And I am on the homepage
    And I wait for the page to be loaded
    And I go to "/app/logout"
    And I wait for the page to be loaded
    And I open the menu
    Then I should see 1 "#btn-profile"
    And I go to "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"
    And the "profile-email__input" field should contain "dev2@catrob.at"
