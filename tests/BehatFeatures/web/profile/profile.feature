@web @profile_page
Feature: As a visitor I want to see public user profiles

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |

  Scenario: Calling the profile route without an id should bring me to myProfile
    Given I log in as "Catrobat"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see "My Profile"
    When I am on "/app/user/"
    And I wait for the page to be loaded
    Then I should see "My Profile"

  Scenario: Calling the profile route with the id 0 should bring me to myProfile
    Given I log in as "Catrobat"
    And I am on "/app/user/0"
    And I wait for the page to be loaded
    Then I should see "My Profile"

  Scenario: Calling the profile route with my user id should bring me to myProfile
    Given I log in as "Catrobat"
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then I should see "My Profile"

  Scenario: Calling the profile route with another id should bring me to the users profile
    Given I log in as "Catrobat"
    And I am on "/app/user/2"
    And I wait for the page to be loaded
    Then I should see "OtherUser"

  Scenario: Trying to get to myProfile when not logged in should bring me to log in page
    Given I am on "/app/user"
    And I wait for the page to be loaded
    Then I should be on "/app/login"