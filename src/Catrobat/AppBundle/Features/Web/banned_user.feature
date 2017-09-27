@homepage
  Feature:
    As a banned user, I should not be able to login. But I should also be unbanned after some time.

  Background:
    Given there are banned users:
      | name     | password  | token      | email               | locked | times_banned | banned_until |
      | Catrobat | 123456    | cccccccccc | dev1@pocketcode.org | 0      | 0            | null         |
      | User1    | 654321    | cccccccccc | dev2@pocketcode.org | 1      | 1            | +2 days      |
      | User2    | bob       | cccccccccc | dev3@pocketcode.org | 1      | 2            | -3 days      |


  Scenario: A banned user should not be able to log in
    Given I am on "/pocketcode/login"
    Then I should be on "/pocketcode/login"
    And I fill in "username" with "User1"
    And I fill in "password" with "654321"
    And I press "Login"
    Then I should see "Your password or username was incorrect."

  Scenario: A banned user who should be automatically unbanned should be able to log in
    Given I am on "/pocketcode/login"
    Then I should be on "/pocketcode/login"
    And I fill in "username" with "User2"
    And I fill in "password" with "bob"
    And I press "Login"
    Then I should be logged in
    And I should see "Catrobat"