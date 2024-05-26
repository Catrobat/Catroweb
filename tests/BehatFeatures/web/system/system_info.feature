@web @system
Feature: Check system variables with admin rights

  Background:
    And there are users:
      | id | name    |
      | 3  | NewUser |
    And there are admins:
      | id | name  |
      | 2  | Admin |

  Scenario: Must not be able to see phpinfo if not logged in
    And I am on "/system/info/php"
    Then I should not see "PHP Version"

  Scenario: Must not be able to see phpinfo if logged in as user
    Given I log in as "NewUser"
    And I am on "/system/info/php"
    Then I should not see "PHP Version"

  Scenario: Must be able to see phpinfo if logged in as admin
    Given I log in as "Admin"
    And I am on "/system/info/php"
    Then I should see "PHP Version"

  Scenario: Must not be able to see db if not logged in
    And I am on "/system/info/db"
    Then I should not see "Database"

  Scenario: Must not be able to see db if logged in as user
    Given I log in as "NewUser"
    And I am on "/system/info/db"
    Then I should not see "Database"

  Scenario: Must be able to see db if logged in as admin
    Given I log in as "Admin"
    And I am on "/system/info/db"
    Then I should see "Database Variable"
