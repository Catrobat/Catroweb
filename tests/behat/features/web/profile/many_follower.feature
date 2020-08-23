@follow_many
Feature: User has a large number of follower (> 100).

  Background:
    Given 501 users follow:
      | id | name      |
      | 1  | user0     |

  Scenario: Notification number in side menu should be appropriate to the number of followers
    Given I log in as "user0"
    And I am on "/app/user"
    And I wait for the page to be loaded
    When I open the menu
    And the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "99+"