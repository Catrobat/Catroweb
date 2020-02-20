@homepage
Feature: User gets generic notifications additionally to the remix notifications

  Background:
    Given there are users:
      | name      | password | token      | email               | id |
      | Catrobat  | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | OtherUser | 123456   | dddddddddd | dev2@pocketcode.org |  2 |

    And there are catro notifications:
      | user     | title                 | message                                         | type        |
      | Catrobat | Achievement - Uploads | Congratulations, you uploaded your first app    | achievement |
      | Catrobat | Achievement - View    | Congratulations, you reached a total of 2 views | achievement |

  Scenario: User views his notifications and sees all of them
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/notifications/allNotifications"
    Then I should see "Achievement - Uploads"
    And I should see "Achievement - View"

  Scenario: User views his notifications marks one as seen and does not see it anymore
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/notifications/allNotifications"
    Then I should see "Achievement - Uploads"
    And I should see "Achievement - View"
    And I open the menu
    And the ".all-notifications-dropdown" element should contain "2"
    When I click "#mark-as-read-1"
    And I wait for fadeEffect to finish
    Then the ".all-notifications-dropdown" element should contain "1"
    When I click "#mark-as-read-2"
    And I wait for fadeEffect to finish
    Then I should see "Old Notifications"
    And I should not see "#mark-as-read-1"
    And I should not see "#mark-as-read-2"
    And I should see "You have no new Notifications"

  Scenario: User should see the amount of his notifications in the menu
    Given I log in as "Catrobat" with the password "123456"
    And I am on "/app/"
    And I open the menu
    Then I wait 1000 milliseconds
    And the element "#notifications-dropdown-toggler" should be visible
    And the "#notifications-dropdown-toggler" element should contain "2"

  Scenario: User should see the amount of his notifications in the menu only if he has notifications
    Given I log in as "OtherUser" with the password "123456"
    And I am on "/app/"
    And I open the menu
    Then the element "#notifications-dropdown-toggler" should be visible
    And the element "#notifications-dropdown-toggler .badge-pill" should not be visible

  Scenario: User should see the amount of his notifications in the menu
    Given there are "105"+ notifications for "Catrobat"
    And I log in as "Catrobat" with the password "123456"
    And I am on "/app/"
    And I open the menu
    Then the element "#notifications-dropdown-toggler" should be visible
    And the element ".all-notifications-dropdown" should be visible
    And the ".all-notifications-dropdown" element should contain "99+"