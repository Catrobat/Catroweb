@web @notifications @remix
Feature: User gets notifications when somebody uploads a remix of his project

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User     |
      | 3  | Drago    |
      | 4  | John     |

    And there are projects:
      | id | name      | owned by | upload time      |
      | 1  | project 1 | Catrobat | 01.01.2013 12:00 |
      | 2  | project 2 | User     | 01.01.2013 12:00 |
      | 3  | project 3 | Catrobat | 01.01.2013 12:00 |
      | 4  | project 4 | Catrobat | 01.01.2013 12:00 |
      | 5  | project 5 | User     | 01.01.2013 12:00 |

  Scenario: Only the project owner should get a notification that his project was remixed
    Given I have a project with "url" set to "/app/project/2"
    And User "Catrobat" uploads the project
    And I log in as "Catrobat"
    When I go to "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see "It looks like you dont have any notifications."
    Given I log in as "User"
    When I go to "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    Then I should see "You have 1 new Notification!"


  Scenario: User should get remix notification
    Given I have a project with "url" set to "/app/project/1"
    When User "Drago" uploads the project
    And I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    And the ".all-notifications-dropdown" element should contain "1"
    And I should see "You have 1 new Notification!"
    And I should see "New remix for project project 1!"
    And I should see "User Drago remixed your project"
    And I should see "You can see the remix on the following link test"


  Scenario: User should get remix notification every time somebody makes a remix of his project
    Given I have a project with "url" set to "/app/project/1"
    When User "Drago" uploads the project
    When I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    And the ".all-notifications-dropdown" element should contain "1"
    And I should see "You have 1 new Notification!"
    And I should see "New remix for project project 1!"
    And I should see "User Drago remixed your project"
    And I should see "You can see the remix on the following link test"
    Given I have a project with "url" set to "/app/project/1"
    And User "User" uploads the project
    And I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    When I open the menu
    Then the ".all-notifications-dropdown" element should contain "2"
    And I should see "You have 2 new Notifications!"
    And I should see "New remix for project project 1!"
    And I should see "User User remixed your project"
    And I should see "You can see the remix on the following link test"


  Scenario: User should get remix notification for remix of any of his projects
    Given I have a project with "url" set to "/app/project/4"
    And User "User" uploads the project
    When I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    And I open the menu
    Then the ".all-notifications-dropdown" element should contain "1"
    And I should see "New remix for project project 4!"
    Given I have a project with "url" set to "/app/project/3"
    And User "John" uploads the project
    And I log in as "Catrobat"
    And I am on "/app/notifications/allNotifications"
    And I wait for the page to be loaded
    When I open the menu
    Then the ".all-notifications-dropdown" element should contain "2"
    And I should see "You have 2 new Notifications!"
    And I should see "New remix for project project 3!"








