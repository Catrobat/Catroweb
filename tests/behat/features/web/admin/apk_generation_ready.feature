@admin
Feature: APK-Generation Pending in Admin Area


  Background:
    Given there are admins:
      | name     | password | token      | email                | id |
      | Adminius | 123456   | eeeeeeeeee | admin@pocketcode.org |  0 |
    And there are users:
      | name     | password | token      | email               | id |
      | Superman | 123456   | cccccccccc | dev1@pocketcode.org |  1 |
      | Gregor   | 123456   | dddddddddd | dev2@pocketcode.org |  2 |
    And there are programs:
      | id | name      | description              | owned by | apk request time | apk_status |
      | 1  | program 1 | my superman description  | Superman | 02.06.2020 10:00 | ready      |
      | 2  | program 2 | abcef                    | Gregor   | 02.06.2020 11:00 | ready      |
      | 3  | program 3 | hello                    | Adminius | 02.06.2020 08:00 | ready      |

  Scenario: List should be complete and sorted after Apk Request Time DESC
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_list/list"
    And I wait for the page to be loaded
    Then I should see the ready apks table:
      | Id | User     | Name      | Apk Request Time   |
      | 2  | Gregor   | program 2 | June 2, 2020 11:00 |
      | 1  | Superman | program 1 | June 2, 2020 10:00 |
      | 3  | Adminius | program 3 | June 2, 2020 08:00 |

  Scenario: The Rebuild button should rebuild apk and set state to pending. Entry shouldn't be in list anymore
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_list/list"
    And I wait for the page to be loaded
    Then I am on "/admin/apk_list/3/rebuildApk"
    And I wait for the page to be loaded
    And I should see the ready apks table:
      | Id | User     | Name      | Apk Request Time   |
      | 2  | Gregor   | program 2 | June 2, 2020 11:00 |
      | 1  | Superman | program 1 | June 2, 2020 10:00 |
    And I should not see "Adminius"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    Then I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 3  | Adminius | program 3 |                    | pending    |
      | 2  | Gregor   | program 2 | June 2, 2020 11:00 | ready      |
      | 1  | Superman | program 1 | June 2, 2020 10:00 | ready      |

  Scenario: The Reset button should reset the apk status, the request time and entry shouldn't be in list anymore
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_list/list"
    And I wait for the page to be loaded
    And I am on "/admin/apk_list/3/resetStatus"
    And I wait for the page to be loaded
    And I should see the ready apks table:
      | Id | User     | Name      | Apk Request Time   |
      | 2  | Gregor   | program 2 | June 2, 2020 11:00 |
      | 1  | Superman | program 1 | June 2, 2020 10:00 |
    And I should not see "Adminius"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    Then I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 3  | Adminius | program 3 |                    | none       |
      | 2  | Gregor   | program 2 | June 2, 2020 11:00 | ready      |
      | 1  | Superman | program 1 | June 2, 2020 10:00 | ready      |
