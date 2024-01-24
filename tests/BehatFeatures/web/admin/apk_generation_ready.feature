@admin
Feature: APK-Generation Pending in Admin Area


  Background:
    Given there are admins:
      | name  | password | token      | email                | id |
      | Admin | 123456   | eeeeeeeeee | admin@pocketcode.org | 1  |
    And there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org | 2  |
      | Gregor   | 123456   | dddddddddd | dev2@pocketcode.org | 3  |
    And there are projects:
      | id | name      | description             | owned by | apk request time | apk_status |
      | 1  | project 1 | my superman description | Superman | 02.06.2020 10:00 | ready      |
      | 2  | project 2 | abcef                   | Gregor   | 02.06.2020 11:00 | ready      |
      | 3  | project 3 | hello                   | Admin    | 02.06.2020 08:00 | ready      |

  Scenario: List should be complete and sorted after Apk Request Time DESC
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/apk_ready/list"
    And I wait for the page to be loaded
    Then I should see the ready apks table:
      | Id | User     | Name      | Apk Request Time   |
      | 2  | Gregor   | project 2 | June 2, 2020 11:00 |
      | 1  | Superman | project 1 | June 2, 2020 10:00 |
      | 3  | Admin    | project 3 | June 2, 2020 08:00 |

  Scenario: The Rebuild button should rebuild apk and set state to pending. Entry shouldn't be in list anymore
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/apk_ready/list"
    And I wait for the page to be loaded
    Then I am on "/admin/apk_ready/3/requestApkRebuild"
    And I wait for the page to be loaded
    When I am on "/admin/apk_ready/list"
    And I wait for the page to be loaded
    Then I should not see "project 3"
    And I wait for the page to be loaded
    When I am on "/admin/apk_pending/list"
    And I wait for the page to be loaded
    Then I should see "project 3"

  Scenario: The Reset button should reset the apk status, the request time and entry shouldn't be in list anymore
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/apk_ready/list"
    And I wait for the page to be loaded
    And I am on "/admin/apk_ready/3/resetApkBuildStatus"
    And I wait for the page to be loaded
    When I am on "/admin/apk_ready/list"
    And I wait for the page to be loaded
    Then I should not see "project 3"
    And I wait for the page to be loaded
    When I am on "/admin/apk_pending/list"
    And I wait for the page to be loaded
    Then I should not see "project 3"
