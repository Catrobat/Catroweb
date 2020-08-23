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
    And user "Catrobat" uploads this generated program, API version 1
    And I log in as "Catrobat"
    When I go to "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "It looks like you don't have any notifications."
    Given I log in as "User"
    When I go to "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Catrobat created a remix"

  Scenario: User should get remix notification
    Given I have a project with "url" set to "/app/project/1"
    When user "Drago" uploads this generated program, API version 1
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "1"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    Then I should see "Drago created a remix"
    And the element "#remix-notif" should be visible
    And I click "#remix-notif"
    And I wait for AJAX to finish
    And I should see "Drago created a remix"


  Scenario: User should get remix notification every time somebody makes a remix of his project
    Given I have a project with "url" set to "/app/project/1"
    When user "Drago" uploads this generated program, API version 1
    When I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "1"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see "Drago created a remix"
    Given I have a project with "url" set to "/app/project/1"
    And user "User" uploads this generated program, API version 1
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "1"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see "User created a remix"


  Scenario: User should get remix notification for remix of any of his projects
    Given I have a project with "url" set to "/app/project/4"
    And user "User" uploads this generated program, API version 1
    When I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "1"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see "User created a remix"
    Given I have a project with "url" set to "/app/project/3"
    And user "John" uploads this generated program, API version 1
    And I log in as "Catrobat"
    And I open the menu
    Then the element "#sidebar-notifications" should be visible
    And the ".all-notifications" element should contain "1"
    And I am on "/app/user_notifications"
    And I wait for the page to be loaded
    And I should see "John created a remix"
