@web @recommendations
Feature: Showing more programs from a user(program owner)

  Background:
    Given there are users:
      | id | name  |
      | 1  | user1 |
      | 2  | user2 |
      | 3  | user3 |
    And there are projects:
      | id | name     | owned by |
      | 1  | user1_p1 | user1    |
      | 2  | user1_p2 | user1    |
      | 3  | user1_p3 | user1    |
      | 4  | user2_p1 | user2    |
      | 5  | user2_p2 | user2    |
      | 6  | user3_p1 | user3    |

  Scenario: There should be more programs from the program owner be recommended
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "More from user1"
    And I should see "user1_p1"
    And I should see "user1_p2"
    And I should see "user1_p3"
    But I should not see "user2_p1"
    And I should not see "user2_p2"
    And I should not see "user3_p1"
    Then I should see 2 "#more-from-this-user-recommendations .program"

  Scenario: When the program owner only has one program don't show the "more-from" category
    When I go to "/app/project/6"
    And I wait for the page to be loaded
    Then I should not see "More from"
    But I should see 0 "#more-from-this-user-recommendations .program"

  Scenario: Not showing more programs when it's your own program
    Given I log in as "user1"
    When I go to "/app/project/1"
    And I wait for the page to be loaded
    Then I should not see "More from"
    But I should see 0 "#more-from-this-user-recommendations .program"

  Scenario: When the user is on a program page the program should not also be recommended in the show more category
    When I go to "/app/project/4"
    And I wait for the page to be loaded
    Then I should see "More from user2"
    And I should see "user2_p2"
    And I should see 1 "#more-from-this-user-recommendations .program"
