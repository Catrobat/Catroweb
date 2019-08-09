@homepage @disabled
Feature: Open Authentication
  I want to be able to sign in as Google user

  Background:
    Given there are users:
      | name        | password | token      | email               | id |
      | Catrobat    | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | AlreadyinDB | 642135   | cccccccccc | dev2@pocketcode.org |  2 |

  @javascript
  Scenario: Try to login with a new user into Google+ where the username already exists
    Given I am on "/app/login"
    When I log in to Google with valid credentials
    And I choose the username 'AlreadyinDB'
    Then I should see "Username already taken, please choose a different one."


  @javascript
  Scenario: It should be possible to change the E-Mail address on the profile page and login again with the same Google+ account
    Given I am on "/app/login"
    When I log in to Google with valid credentials
    And I choose the username 'PocketGoogler'
    Then I should be logged in
    And there is a user in the database:
      | name          | email                      | google_uid            | google_name | country |
      | PocketGoogler | pocketcodetester@gmail.com | 105155320106786463089 |             | de      |
    And I am on "/app/emailEdit"
      | name          | email                      | facebook_uid | facebook_name | google_uid            | google_name | country |
      | PocketGoogler | pocketcodetester@gmail.com |              |               | 105155320106786463089 |             | de      |
    And I am on "/app/emailEdit"
    Then I wait for the server response
    Then I fill in "email" with "pocket-code-tester@gmail.com"
    When I click the "save-edit" button
    And I wait for the server response
    Then I should be on "/app/user/edit"
    Then the "#email-text" element should contain "pocket-code-tester@gmail.com"
    When I go to "/logout"
    Then I should not be logged in
    When I trigger Google login with approval prompt "auto"
    And I click Google login link "once"
    And I wait for the server response
    Then I should be logged in
    And I am on "/app/user"
    Then the "#email" element should contain "pocket-code-tester@gmail.com"
