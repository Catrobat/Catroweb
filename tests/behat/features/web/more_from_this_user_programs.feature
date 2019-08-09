@homepage
Feature: Showing more programs from a user(program owner)

  Background:
    Given there are users:
      | name     | password | token      | email               |
      | Catrobat | 123456   | cccccccccc | dev1@pocketcode.org |
      | user2    | 123456   | cccccccccc | dev2@pocketcode.org |
      | user3    | 123456   | cccccccccc | dev3@pocketcode.org |
    And there are programs:
      | id | name     | description | owned by | downloads | apk_downloads | views | upload time      | version |
      | 1  | user1_p1 | p1          | Catrobat | 3         | 2             | 12    | 01.01.2013 12:00 | 0.8.5   |
      | 2  | user1_p2 | p2          | Catrobat | 10        | 12            | 13    | 01.02.2013 12:00 | 0.8.5   |
      | 3  | user1_p3 | p3          | Catrobat | 5         | 55            | 2     | 01.03.2013 12:00 | 0.8.5   |
      | 4  | user2_p1 | p5          | user2    | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |
      | 5  | user2_p2 | p6          | user2    | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |
      | 6  | user3_p1 | p7          | user3    | 5         | 1             | 1     | 01.03.2013 12:00 | 0.8.5   |

  Scenario: There should be more programs from the program owner be recommended
    When I go to "/app/program/1"
    Then I should see "More from Catrobat"
    And I should see "user1_p1"
    And I should see "user1_p2"
    And I should see "user1_p3"
    But I should not see "user2_p1"
    But I should not see "user2_p2"
    But I should not see "user3_p1"
    Then I should see 2 "#more-from-this-user-recommendations .program"

  Scenario: When the program owner only has one program don't show the "more-from" category
    When I go to "/app/program/6"
    Then I should not see "More from"
    Then I should see 0 "#more-from-this-user-recommendations .program"

  Scenario: Not showing more programs when it's your own program
    Given I am on "app/login"
    When I fill in "username" with "Catrobat"
    And I fill in "password" with "123456"
    And I press "Login"
    When I go to "/app/program/1"
    Then I should not see "More from"
    Then I should see 0 "#more-from-this-user-recommendations .program"

  Scenario: When the user is on a program page the program should not also be recommended in the show more category
    When I go to "/app/program/4"
    Then I should see "More from user2"
    But I should see "user2_p2"
    Then I should see 1 "#more-from-this-user-recommendations .program"
