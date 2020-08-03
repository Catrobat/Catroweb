@web @notifications
Feature: Sidebar should show amount of new notifications in form of a badge

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | User      |
      | 3  | Drago     |
      | 4  | John      |
      | 5  | OtherUser |

    And there are projects:
      | id | name      | owned by |
      | 1  | program 1 | Catrobat |
      | 2  | program 2 | User     |
      | 3  | program 3 | Drago    |

  Scenario: User should see the amount of his notifications in the menu only if he has notifications
    Given I log in as "OtherUser"
    And I am on "/app/"
    And I wait for the page to be loaded
    When I open the menu
    Then the element "#sidebar-notifications" should be visible
    But the element "#sidebar-notifications .badge-pill" should not be visible

  Scenario: User should see new Notifications and the badge should contain number of new notifications
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "5"

  Scenario: User should see the amount of his notifications in the menu (capped at 99+)
    Given there are "105"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I am on "/app/"
    And I wait for the page to be loaded
    When I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the element ".all-notifications" should be visible
    And the ".all-notifications" element should contain "99+"

  Scenario: If user marks the notification as read then the badge should be updated
    Given there are "5"+ notifications for "Catrobat"
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "5"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I open the menu
    Then the element ".all-notifications" should not be visible

  Scenario: If the user who I am following uploads the program then the notification badge should be incremented
    Given I log in as "Catrobat"
    And I am on "/app/user/2"
    And I wait for the page to be loaded
    And I click ".profile-follow"
    And I wait for AJAX to finish
    And I am on "/app/user/3"
    And I wait for the page to be loaded
    And I click ".profile-follow"
    And I wait for AJAX to finish
    Given I have a project with "url" set to "/app/project/1"
    And user "User" uploads this generated program, API version 1
    And user "Drago" uploads this generated program, API version 1
    Given I log in as "Catrobat"
    And I am on "/app/"
    And I open the menu
    And I wait for AJAX to finish
    Then the ".all-notifications" element should contain "4"
