@web @follow
Feature: Follow feature on profiles

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | Catrobat2 |
      | 3  | Catrobat3 |
      | 4  | Catrobat4 |
      | 5  | Catrobat5 |
      | 6  | Catrobat6 |
      | 7  | Catrobat7 |
      | 8  | Catrobat8 |

  Scenario: Follow button and counter should show up on Profile site
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element "#profile-follow-btn" should be visible
    And I should see text matching "Follower:"

  Scenario: Follow when not logged in should redirect to login
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#profile-follow-btn"
    And I wait for the page to be loaded
    Then I should be on "/app/login"

  Scenario: Follow user should follow and increase counter
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I should see text matching "Follow"
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And the element "#profile-follow-btn" should be visible
    And I should see text matching "Following"
    Then I should see "Follower: 1"

  Scenario: Unfollow user should unfollow and decrease counter
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And I should see text matching "Following"
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And the element "#follow-btn" should be visible
    And I should see text matching "Follow"
    And I should see text matching "Follower: 0"

  Scenario: Following section should show appropriate information:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    Then I am on "/app/follower"
    And I wait for the page to be loaded
    And I should see text matching "FOLLOWING"
    And I should see text matching "FOLLOWERS"
    And I should see text matching "Catrobat"
    And the element "#follow-btn" should be visible
    And I should see text matching "Following"

  Scenario: Follower section should show appropriate information:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I log in as "Catrobat"
    And I am on "/app/follower"
    And I wait for the page to be loaded
    And I should see text matching "FOLLOWING"
    And I should see text matching "FOLLOWERS"
    And I should see text matching "Catrobat2"
    And the element "#follow-btn" should be visible
    And I should see text matching "Follow"


  Scenario: Following other users should be possible directly from the Follower page:
    Given I log in as "Catrobat2"
    And I am on "/app/follower"
    And I wait for the page to be loaded
    But the element "#follow-btn" should not exist
    And I should not see "Catrobat3"
    Then I log in as "Catrobat3"
    And I am on "/app/user/2"
    Then I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I am on "/app/follower"
    And I wait for the page to be loaded
    And I should see text matching "FOLLOWING"
    And I should see text matching "FOLLOWERS"
    And I should see text matching "Catrobat2"
    And the element "#follow-btn" should be visible
    And I should see text matching "Following"
    Then I log in as "Catrobat2"
    And I am on "/app/user/3"
    And I wait for the page to be loaded
    Then I should see text matching "Follower: 0"
    And I am on "/app/follower"
    And I wait for the page to be loaded
    And the element "#follow-btn" should exist
    And I should see text matching "Catrobat3"
    And I should see text matching "Follow"
    And I click "#follow-btn"
    And I wait for AJAX to finish
    Then I am on "/app/user/3"
    And I wait for the page to be loaded
    And I should see text matching "Follower: 1"


  Scenario: Following sends a notification:
    Given I log in as "Catrobat2"
    And I am on "/app/user/3"
    And I wait for the page to be loaded
    Then I should see text matching "Follow"
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/user/3"
    Then I should see text matching "Following"
    Then I log in as "Catrobat3"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat2 follows you now"
    Given I am on "/app/notifications/followers"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat2 follows you now"

  Scenario: Users should be able to follow/unfollow other users from the "Followers" notification page:
    Given I log in as "Catrobat2"
    And I am on "/app/user/3"
    And I wait for the page to be loaded
    Then I should see text matching "Follow"
    And I click "#profile-follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/user/3"
    Then I should see text matching "Following"
    Then I log in as "Catrobat3"
    Given I am on "/app/notifications/followers"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat2 follows you now"
    And the element "#follow-btn-notif" should be visible
    And I should see text matching "Follow"
    Then I click "#follow-btn-notif"
    And I wait for AJAX to finish
    Then I should see text matching "Following"
    And I am on "/app/user/2"
    And I wait for the page to be loaded
    Then I should see text matching "Follower: 1"
    Then I log in as "Catrobat2"
    Given I am on "/app/notifications/followers"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat3 follows you now"