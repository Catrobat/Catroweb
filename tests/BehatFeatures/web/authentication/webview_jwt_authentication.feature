@web @security
Feature: Users should be logged in automatically when they are logged in in the app

  Background:
    Given there are users:
      | id | name        | email          |
      | 1  | WebViewUser | dev1@catrob.at |

  Scenario: Log in using Catrobat user and show profile (cookie)
    Given I use a valid BEARER cookie for "WebViewUser"
    When I go to "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"
    And the "profile-email__input" field should contain "dev1@catrob.at"

  Scenario: Log in using Catrobat user and show profile (cookie)
    Given I set the cookie "BEARER" to "invalid"
    When I go to "/app/user"
    And I wait for the page to be loaded
    Then I should be on "app/"

  Scenario: Log in using Catrobat user and show profile
    Given I use a valid JWT token for "WebViewUser"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"
    And the "profile-email__input" field should contain "dev1@catrob.at"

  Scenario: Log in using Catrobat user with wrong token
    Given I use an invalid JWT authorization header for "WebViewUser"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "WebViewUser"
    Then I should not see "My Profile"

  Scenario: Log in using empty token should be ignored
    Given I use an empty JWT token for "WebViewUser"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "My Profile"
    And I should be on "/app/login"
    And I should see 1 "#password"

  Scenario: Log in without user auth header should be ignored
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should not see "My Profile"
    And I should be on "/app/login"
    And I should see 1 "#password__input"

  Scenario: Logout button should be hidden in webview
    Given I use a valid JWT token for "WebViewUser"
    And I use a release build of the Catroid app
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    Then I should see 0 "#btn-logout"
    Then I should see 0 "#btn-login"
    Then I should see 1 "#btn-profile"

  Scenario: Logout should not be possible
    Given I use a valid JWT token for "WebViewUser"
    And I am on the homepage
    And I wait for the page to be loaded
    And I go to "/app/logout"
    And I wait for the page to be loaded
    And I open the menu
    Then I should see 1 "#btn-profile"
    And I go to "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"
    And the "profile-email__input" field should contain "dev1@catrob.at"
