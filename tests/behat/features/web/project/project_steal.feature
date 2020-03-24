@homepage
Feature: Steal program

  Background:
    Given there are users:
      | id | name       | password |
      | 1  | Catrobat   | 1234     |
      | 2  | OtherUser  | 12345    |

    And there are projects:
      | id | name     | description | owned by  |
      | 1  | Minions  | p1          | Catrobat  |
      | 2  | Minimies | p2          | Catrobat  |
      | 3  | otherPro | p3          | OtherUser |


  Scenario: Clicking on the steal apk button should change owner of the project
    Given I log in as "OtherUser" with the password "12345"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "Steal this project"
    And the "#icon-author .icon-text" element should contain "Catrobat"
    When I click "#apk-steal"
    And I wait for the page to be loaded
    Then the "#icon-author .icon-text" element should contain "OtherUser"