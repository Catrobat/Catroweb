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
      | 1  | program 1 | my superman description  | Superman | 27.05.2020 10:00 | ready      |
      | 2  | program 2 | abcef                    | Gregor   | 27.05.2020 11:00 | none       |
      | 3  | program 3 | hello                    | Adminius |                  | none       |

  Scenario: List should be complete and sorted after Apk Request Time DESC
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    Then I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 2  | Gregor   | program 2 | May 27, 2020 11:00 | none       |
      | 1  | Superman | program 1 | May 27, 2020 10:00 | ready      |
      | 3  | Adminius | program 3 |                    | none       |

  Scenario: The rebuild button should rebuild apk and set state to pending
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    Then I am on "/admin/apk_pending_requests/3/rebuildApk"
    And I wait for the page to be loaded
    And I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 3  | Adminius | program 3 |                    | pending    |
      | 2  | Gregor   | program 2 | May 27, 2020 11:00 | none       |
      | 1  | Superman | program 1 | May 27, 2020 10:00 | ready      |

  Scenario: The reset button should reset the apk status and the request time
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    And I am on "/admin/apk_pending_requests/3/rebuildApk"
    And I wait for the page to be loaded
    And I am on "/admin/apk_pending_requests/2/rebuildApk"
    And I wait for the page to be loaded
    Then I am on "/admin/apk_pending_requests/3/resetStatus"
    And I wait for the page to be loaded
    And I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 2  | Gregor   | program 2 |                    | pending    |
      | 1  | Superman | program 1 | May 27, 2020 10:00 | ready      |
      | 3  | Adminius | program 3 |                    | none       |
    And I should not see "May 27, 2020 11:00"

  Scenario: The reset all button should reset all programs
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    And I am on "/admin/apk_pending_requests/2/rebuildApk"
    And I wait for the page to be loaded
    Then I am on "/admin/apk_pending_requests/resetAllApk"
    And I wait for the page to be loaded
    And I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 1  | Superman | program 1 |                    | none       |
      | 2  | Gregor   | program 2 |                    | none       |
      | 3  | Adminius | program 3 |                    | none       |
    And I should not see "May 27, 2020 10:00"
    And I should not see "May 27, 2020 11:00"

  Scenario: The rebuild all button should rebuild all programs
    Given I log in as "Adminius" with the password "123456"
    And I am on "/admin/apk_pending_requests/list"
    And I wait for the page to be loaded
    Then I am on "/admin/apk_pending_requests/rebuildAllApk"
    And I wait for the page to be loaded
    And I should see the pending apk table:
      | Id | User     | Name      | Apk Request Time   | Apk Status |
      | 3  | Adminius | program 3 |                    | pending    |
      | 2  | Gregor   | program 2 |                    | pending    |
      | 1  | Superman | program 1 |                    | pending    |
    And I should not see "May 27, 2020 10:00"
    And I should not see "May 27, 2020 11:00"
