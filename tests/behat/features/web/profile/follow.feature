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

  Scenario: Follow button and counter should show up on Profile
    Given I log in as "Catrobat"
    And I am on "/app/user/2"
    And I wait for the page to be loaded
    And the element ".button-container" should be visible
    And the element ".profile-follow" should be visible
    And the element ".profile-follows" should not be visible

  Scenario: Follow when not logged in should redirect to login
    Given I am not logged in
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follow" should be visible
    And I click ".profile-follow"
    And I wait for the page to be loaded
    Then I should be on "/app/login"

  Scenario: Follow user should follow and increase counter
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And I should see text matching "Follow"
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follow" should not be visible
    And the element ".profile-follows" should be visible
    And I should see text matching "0 Followers"

  Scenario: Unfollow user should unfollow and decrease counter
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follow" should be visible
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And I should see text matching "Following"
    And the element ".profile-follows" should be visible
    And I click ".profile-follows"
    And I wait for AJAX to finish
    And the element ".swal2-shown" should be visible
    And I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follows" should not be visible
    And the element ".profile-follow" should be visible
    And I should see text matching "0 Followers"

  Scenario: Following section should show appropriate information:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follow" should be visible
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    Then I am on "/app/follower"
    And the element "#follows-tab" should be visible
    And the element "#follower-tab" should be visible
    And I click "#follows-tab"
    And I wait for the page to be loaded
    And I should see text matching "Following"
    And I should see text matching "Followers"
    And I should see text matching "Catrobat"
    And the element ".following-item-1" should be visible
    And the element ".following-item-1 .unfollow-btn" should be visible
    And I should see text matching "Following"

  Scenario: Follower section should show appropriate information:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follow" should be visible
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I am on "/app/follower"
    And the element "#follows-tab" should be visible
    And the element "#follower-tab" should be visible
    And I click "#follows-tab"
    And I should see text matching "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/follower"
    And I wait for the page to be loaded
    And the element "#follows-tab" should be visible
    And the element "#follower-tab" should be visible
    And I click "#follower-tab"
    And the element ".follower-item-2" should be visible
    And the element ".follower-item-2 .follow-btn" should be visible
    And I should see text matching "Catrobat2"
    And the element ".follow-btn" should be visible

  Scenario: Following other users should be possible directly from the Follower page:
    Given I log in as "Catrobat2"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    And the element ".profile-follow" should be visible
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Then I should be on "/app/user/1"
    And I am on "/app/follower"
    And the element "#follows-tab" should be visible
    And the element "#follower-tab" should be visible
    And I click "#follows-tab"
    And the element ".following-item-1" should be visible
    And the element ".following-item-1 .unfollow-btn" should be visible
    And I should see text matching "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/follower"
    And I wait for the page to be loaded
    And the element "#follows-tab" should be visible
    And the element "#follower-tab" should be visible
    And I click "#follower-tab"
    And the element ".follower-item-2" should be visible
    And the element ".follower-item-2 .follow-btn" should be visible
    And the element ".following-item-2" should not exist
    And the element ".following-item-2 .unfollow-btn" should not exist
    And I should see text matching "Catrobat2"
    And I click ".follower-item-2 .follow-btn"
    And I wait for AJAX to finish
    Then I should be on "/app/follower"
    And I wait for the page to be loaded
    And the element ".following-item-2" should not be visible
    And the element ".following-item-2 .unfollow-btn" should not be visible
    And I click "#follows-tab"
    And the element ".following-item-2" should be visible
    And the element ".following-item-2 .unfollow-btn" should be visible
    And the element ".follower-item-2" should not be visible
    And the element ".follower-item-2 .follow-btn" should not be visible

  Scenario: Following sends a notification:
    Given I log in as "Catrobat2"
    And I am on "/app/user/3"
    And I wait for the page to be loaded
    Then I should see text matching "Follow"
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Then I should be on "/app/user/3"
    Then I should see text matching "Following"
    Then I log in as "Catrobat3"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see text matching "Catrobat2 is now following you."
    And the element "#all-notif" should be visible
    And the element "#follow-notif" should be visible
    And the element "#reaction-notif" should be visible
    And the element "#comment-notif" should be visible
    And the element "#remix-notif" should be visible
    And I click "#follow-notif"
    And I wait for AJAX to finish
    Then I should see text matching "Catrobat2 is now following you."
