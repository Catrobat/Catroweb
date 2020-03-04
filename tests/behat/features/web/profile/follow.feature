@web @follow
Feature: Follow feature on profiles

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | Catrobat2 |

  Scenario: Follow button and counter should show up on Profile site
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element "#follow-btn" should be visible
    And I should see text matching "Follower:"

  Scenario: Follow when not logged in should redirect to login
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/login"

  Scenario: Follow user should follow and increase counter
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And the element "#follow-btn" should be visible
    And I should see text matching "Follower: 1"
    And Element "#follow-btn" should have attribute "title" with value "Unfollow this user!"

  Scenario: Unfollow user should unfollow and decrease counter
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And the element "#follow-btn" should be visible
    And I should see text matching "Follower: 0"
    And Element "#follow-btn" should have attribute "title" with value "Follow this user!"

  Scenario: Follower and Following should show on my profile:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"
    Then I log in as "Catrobat"
    And I am on "/app/user/2"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/user/2"
    Then I am on "/app/user"

  Scenario: Following sends Notification:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/user/1"
    Then I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat2 follows you now"
    Given I am on "/app/notifications/followers"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat2 follows you now"
