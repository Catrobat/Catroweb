@follow_many
Feature: User has a large number of follower (> 100).

  Background:
    Given 501 users follow:
      | id | name      |
      | 1  | user0     |

  Scenario: Follower notification number in side menu should be appropriate to the number of followers
    Given I log in as "user0"
    And I am on "/app/user"
    And I wait for the page to be loaded
    When I open the menu
    Then the element ".collapsible" should be visible
    And the element "#notifications-dropdown-arrow" should be visible
    And the "#notifications-dropdown-arrow" element should contain "chevron_left"
    When I click ".collapsible"
    And I wait for AJAX to finish
    And the "#notifications-dropdown-arrow" element should contain "expand_more"
    And the ".all-notifications" element should contain "99+"
    And the ".followers" element should contain "99+"