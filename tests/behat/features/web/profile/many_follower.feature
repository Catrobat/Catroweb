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
    And the element ".fa-caret-left" should be visible
    When I click ".collapsible"
    And I wait for AJAX to finish
    Then the element ".fa-caret-down" should be visible
    And the ".all-notifications" element should contain "99+"
    And the ".followers" element should contain "99+"