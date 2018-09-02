@homepage @disabled
Feature: Open Authentication
  I want to be able to sign in as Facebook and Google+ user

  Background:
    Given there are users:
      | name            | password  | token      | email                           |
      | Catrobat        | 123456    | cccccccccc | dev1@pocketcode.org             |
      | Google-Dev      | 654321    | cccccccccc | pocketcodetester@gmail.com      |
      | FB-Dev          | 135246    | cccccccccc | pocket_zlxacqt_tester@tfbnw.net |

  @javascript @insulated
  Scenario: Login as a new user from Facebook, where another user without Facebook-ID, but with same E-Mail already exists.
            Facebook account should be auto-connected to existing account.
    Given I am on "/pocketcode/login"
    When I log in to Facebook with valid credentials
    Then I should be logged in
    And there is a user in the database:
      | name              | email                              | facebook_uid      | facebook_name | google_uid             | google_name        |country |
      | FB-Dev            | pocket_zlxacqt_tester@tfbnw.net    | 105678789764016   |               |                        |                    | at     |
    And I go to "/logout"
    Then I should not be logged in

  @javascript @insulated
  Scenario: Login as a new user from Google+, where another user without Google+-ID, but with same E-Mail already exists.
            Google+ account should be auto-connected to existing account.
    Given I am on "/pocketcode/login"
    When I log in to Google with valid credentials
    Then I should be logged in
    And there is a user in the database:
      | name              | email                              | facebook_uid      | facebook_name | google_uid             | google_name        |country |
      | Google-Dev        | pocketcodetester@gmail.com         |                   |               | 105155320106786463089  |                    | at     |
    And I go to "/logout"
    Then I should not be logged in
