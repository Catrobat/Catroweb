@admin
Feature: Admin All Extensions

  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org |  1 |
    And there are extensions:
      | id | name         | prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |

  Scenario: All extensions should be displayed
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/all_extensions/list"
    And I wait for the page to be loaded
    Then I should see the extensions table:
      | Id | Name         | Prefix  |
      | 1  | Arduino      | ARDUINO |
      | 2  | Drone        | DRONE   |
      | 3  | Lego         | LEGO    |
      | 4  | Phiro        | PHIRO   |
      | 5  | Raspberry Pi | RASPI   |

  Scenario: Edit button should lead to edit page
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/all_extensions/list"
    And I wait for the page to be loaded
    And I click on the edit button of the extension number "1" in the extensions list
    Then I should be on "/admin/all_extensions/1/edit"

  Scenario: Create extensions button
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/all_extensions/list"
    And I wait for the page to be loaded
    And I am on "/admin/all_extensions/extensions"
    And I wait for the page to be loaded
    Then I should see "Creating extensions finished!"

  Scenario: Add new button should take me to create page
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/all_extensions/list"
    And I wait for the page to be loaded
    And I click on the add new button
    And I wait for the page to be loaded
    Then I should be on "/admin/all_extensions/create"


