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
      | id | name      | description       | owned by | apk request time | apk_status |
      | 1  | program 1 | ready program     | Superman |                  | ready      |
      | 2  | program 2 | none program      | Gregor   |                  | none       |
      | 3  | program 3 | pending program 1 | Admin    | 27.05.2020 10:00 | pending    |
      | 4  | program 4 | pending program 2 | Admin    | 27.05.2020 11:00 | pending    |

  Scenario: List should be complete and sorted after Apk Request Time DESC
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/catrobat/apk/pending/list"
    And I wait for the page to be loaded
    Then I should see the pending apk table:
      | Id | User  | Name      | Apk Request Time | Apk Status |
      | 3  | Admin | program 3 |                  | pending    |
      | 4  | Admin | program 4 |                  | pending    |

  Scenario: The rebuild button should rebuild apk and set state to pending
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/catrobat/apk/pending/list"
    And I wait for the page to be loaded
    Then I am on "/admin/catrobat/apk/pending/3/requestApkRebuild"
    And I wait for the page to be loaded
    And I should see the pending apk table:
      | Id | User  | Name      | Apk Request Time | Apk Status |
      | 3  | Admin | program 3 |                  | pending    |
      | 4  | Admin | program 4 |                  | pending    |

  Scenario: The reset button should reset the apk status and the request time
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/catrobat/apk/pending/list"
    And I wait for the page to be loaded
    Then I am on "/admin/catrobat/apk/pending/3/resetApkBuildStatus"
    And I wait for the page to be loaded
    And I am on "/admin/catrobat/apk/pending/list"
    And I wait for the page to be loaded
    And I should not see "program 3"

  Scenario: The reset all button should reset all programs
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/catrobat/apk/pending/list"
    And I wait for the page to be loaded
    Then I am on "/admin/catrobat/apk/pending/resetPendingProjects"
    And I wait for the page to be loaded
    And I should not see "program 3"
    And I should not see "program 4"

  Scenario: The rebuild all button should rebuild all programs
    Given I log in as "Admin" with the password "123456"
    And I am on "/admin/catrobat/apk/pending/list"
    And I wait for the page to be loaded
    Then I am on "/admin/catrobat/apk/pending/rebuildAllApk"
    And I wait for the page to be loaded
    And I should see the pending apk table:
      | Id | User  | Name      | Apk Request Time | Apk Status |
      | 3  | Admin | program 3 |                  | pending    |
      | 4  | Admin | program 4 |                  | pending    |
    And I should not see "May 27, 2020 10:00"
    And I should not see "May 27, 2020 11:00"
