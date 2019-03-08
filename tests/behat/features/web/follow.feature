@follow
Feature: Follow feature on profiles

  Background:
    Given there are users:
      | name       | password | token      | email                |
      | Catrobat   | 123456   | cccccccccc | dev1@pocketcode.org  |
      | Catrobat2  | 123456   | dddddddddd | dev2@pocketcode.org  |
      | Catrobat3  | 123456   | eeeeeeeeee | dev3@pocketcode.org  |
      | Catrobat4  | 123456   | ffffffffff | dev4@pocketcode.org  |
      | Catrobat5  | 123456   | gggggggggg | dev5@pocketcode.org  |
      | Catrobat6  | 123456   | hhhhhhhhhh | dev6@pocketcode.org  |
      | Catrobat7  | 123456   | iiiiiiiiii | dev7@pocketcode.org  |
      | Catrobat8  | 123456   | jjjjjjjjjj | dev8@pocketcode.org  |
      | Catrobat9  | 123456   | kkkkkkkkkk | dev9@pocketcode.org  |
      | Catrobat10 | 123456   | llllllllll | dev10@pocketcode.org |
      | Catrobat11 | 123456   | mmmmmmmmmm | dev11@pocketcode.org |
      | Catrobat12 | 123456   | nnnnnnnnnn | dev12@pocketcode.org |
      | Catrobat13 | 123456   | oooooooooo | dev13@pocketcode.org |
      | Catrobat14 | 123456   | pppppppppp | dev14@pocketcode.org |
      | Catrobat15 | 123456   | qqqqqqqqqq | dev15@pocketcode.org |
      | Catrobat16 | 123456   | rrrrrrrrrr | dev16@pocketcode.org |

  Scenario: Follow button and counter should show up on Profile site
    Given I am on "/pocketcode/profile/1"
    And the element "#follow-btn" should be visible
    And I should see text matching "Follower:"

  Scenario: Follow when not logged in should redirect to login
    Given I am on "/pocketcode/profile/1"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/login"

  Scenario: Follow user should follow and increase counter
    Given I log in as "Catrobat2" with the password "123456"
    And I am on "/pocketcode/profile/1"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/profile/1"
    And the element "#follow-btn" should be visible
    And I should see text matching "Follower: 1"
    And Element "#follow-btn" should have attribute "title" with value "Unfollow this user!"

  Scenario: Unfollow user should unfollow and decrease counter
    Given I log in as "Catrobat2" with the password "123456"
    And I am on "/pocketcode/profile/1"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/profile/1"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/profile/1"
    And the element "#follow-btn" should be visible
    And I should see text matching "Follower: 0"
    And Element "#follow-btn" should have attribute "title" with value "Follow this user!"

  Scenario: Follower and Following should show on my profile:
    Given I log in as "Catrobat2" with the password "123456"
    And I am on "/pocketcode/profile/1"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/profile/1"
    Then I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/profile/2"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/profile/2"
    Then I am on "/pocketcode/profile"

  Scenario: Following sends Notification:
    Given I log in as "Catrobat2" with the password "123456"
    And I am on "/pocketcode/profile/1"
    And I click "#follow-btn"
    Then I should be on "/pocketcode/profile/1"
    Then I log in as "Catrobat" with the password "123456"
    And I am on "/pocketcode/user/notifications"
    Then I should see text matching "Catrobat2 follows you now"