@web @notifications
Feature: Sidebar should have a dropdown for all notification categories

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

  Scenario: User should see notifications dropdown and if he clicks on it he should see subsections
    Given I log in as "Catrobat"
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    And the element ".collapsible" should be visible
    And the element ".fa-caret-left" should be visible
    When I click ".collapsible"
    And I wait for AJAX to finish
    Then the element ".fa-caret-down" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    And the element "#notifications-dropdown-content #btn-remix" should be visible


  Scenario: User should not see all notification subsections on the homepage
    Given I log in as "Catrobat"
    And I am on the homepage
    And I wait for the page to be loaded
    And I open the menu
    And the element "#notifications-dropdown-content a" should not be visible
    And the element "#notifications-dropdown-content #btn-notifications" should not be visible
    And the element "#notifications-dropdown-content #btn-followers" should not be visible
    And the element "#notifications-dropdown-content #btn-likes" should not be visible
    And the element "#notifications-dropdown-content #btn-comments" should not be visible
    And the element "#notifications-dropdown-content #btn-remix" should not be visible


  Scenario: User should see all notification subsections on notification pages
    Given I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    And the element "#notifications-dropdown-content #btn-remix" should be visible
    Given I am on "/app/notifications/followers"
    And I wait for the page to be loaded
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    And the element "#notifications-dropdown-content #btn-remix" should be visible
    Given I am on "/app/notifications/likes"
    And I wait for the page to be loaded
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    And the element "#notifications-dropdown-content #btn-remix" should be visible
    Given I am on "/app/notifications/comments"
    And I wait for the page to be loaded
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    And the element "#notifications-dropdown-content #btn-remix" should be visible
    Given I am on "/app/notifications/remix"
    And I wait for the page to be loaded
    And I open the menu
    And the element "#notifications-dropdown-content a" should be visible
    And the element "#notifications-dropdown-content #btn-notifications" should be visible
    And the element "#notifications-dropdown-content #btn-followers" should be visible
    And the element "#notifications-dropdown-content #btn-likes" should be visible
    And the element "#notifications-dropdown-content #btn-comments" should be visible
    And the element "#notifications-dropdown-content #btn-remix" should be visible
