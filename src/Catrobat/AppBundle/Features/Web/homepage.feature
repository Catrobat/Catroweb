@homepage
Feature: Pocketcode homepage
  In order to access and browse the programs
  As a visitor
  I want to be able to see the homepage

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 123456    | cccccccccc |
      | User1    | 654321    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | program 2 |             | Catrobat | 333       | 9     | 22.04.2014 13:00 | 0.8.5   |
      | 3  | program 3 |             | User1    | 133       | 33    | 01.01.2012 13:00 | 0.8.5   |

  Scenario: Viewing the homepage at website root
    Given I am on homepage
    Then I should see the featured slider
    And I should see newest programs
    And I should see most downloaded programs
    And I should see most viewed programs

  Scenario: Login and logout
    Given I am on homepage
    Then I should see an "#btn-login" element
    When I click the "login" button
    Then I should be on "/pocketcode/login"
    And I should see an "#header-logo" element
    And I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    Then I press "Login"
    Then I should be logged in


  #Facebook and Google Scenarios only works with PhantommJS >= 2.0.
  #In previous versions there was a bug that 'clicks cannot be completed' when switching back from the Login popup
  #windows. There is also a bug with PhantomJS 2.0 where file uploads do not work (should be fixed soon)
  #This bug can be fixed in QT --> processingUserGesture() in UserGestureIndicator.h must return true! see https://github.com/ariya/phantomjs/pull/12896/files
  #This is because when testing with PhantomJS it's no User Gesture but a programmatic gesture (which must be allowed when testing)!
  @Insulated
  Scenario: Login with a new user into Facebook, logout and login again with the now existing user
    Given I am on homepage
    When I trigger Facebook login with auth_type 'reauthenticate'
    And I switch to popup window
    Then I log in to Facebook with valid credentials
    And I choose the username 'HeyWickieHey'
    Then I should be logged in
    And there is a user in the database:
      | name              | email                              | facebook_uid      | facebook_name | google_uid             | google_name        |country |
      | HeyWickieHey      | pocket_zlxacqt_tester@tfbnw.net    | 105678789764016   |               |                        |                    | en_US  |
    And I should see an "#btn-logout" element
    When I click the "logout" button
    Then I should not be logged in
    When I trigger Facebook login with auth_type ''
    And I wait for a second
    Then I should be logged in

  @Insulated
  Scenario: Login with a new user into Google, logout and login again with the now existing user
    Given I am on homepage
    When I trigger Google login with approval prompt "force"
    And I switch to popup window
    Then I log in to Google with valid credentials
    And I choose the username 'PocketGoogler'
    Then I should be logged in
    And there is a user in the database:
      | name              | email                              | facebook_uid      | facebook_name | google_uid             | google_name        |country |
      | PocketGoogler     | pocketcodetester@gmail.com         |                   |               | 105155320106786463089  |                    | de     |
    And I should see an "#btn-logout" element
    When I click the "logout" button
    Then I should not be logged in
    When I trigger Google login with approval prompt "auto"
    And I wait for a second
    Then I should be logged in
