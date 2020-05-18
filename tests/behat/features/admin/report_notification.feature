@admin
Feature: Admin Report Notification
  In order to get informed of an upload
  As a subscribed admin
  I want to get an email when a program is uploaded or reported

  Background:
    Given there are admins:
      | name     | email           | id | password |
      | catroweb | admin@catrob.at |  0 | catroweb |
    Given there are users:
      | name     | email           | id | password |
      | Catrobat | catro@catrob.at |  1 | 123456   |
      | User1    | dog@catrob.at   |  2 | 123456   |
      | User2    | dog2@catrob.at  |  3 | 123456   |
    And there are programs:
      | id | name      |
      | 1  | program 1 |
      | 2  | program 2 |
      | 3  | program 3 |
    And there are notifications:
      | user     | upload | report | summary |
      | Catrobat | 1      | true   | 0       |
      | User1    | 1      | 0      | 0       |
      | User2    | 0      | true   | 0       |

  Scenario: Email subscribed admins directly after report
    Given I activate the Profiler
    When I log in as "Catrobat" with the password "123456"
    And I report program 1 with category "spam" and note "Bad Program" in Browser
    Then I should see 2 outgoing emails
    And I should see a email with recipient "admin@catrob.at"
    And I should see a email with recipient "dog2@catrob.at"

  Scenario: Email subscribed admins directly after upload
    Given I activate the Profiler
    When I log in as "Catrobat" with the password "123456"
    Given I have a parameter "username" with value "Catrobat"
    And I have a parameter "token" with value "cccccccccc"
    And I have a valid Catrobat file, API version 1
    And I have a parameter "fileChecksum" with the md5checksum of "test.catrobat"
    When I POST these parameters to "/app/api/upload/upload.json"
    Then I should see 2 outgoing emails
    And I should see a email with recipient "admin@catrob.at"
    And I should see a email with recipient "dog@catrob.at"

  Scenario: Change upload and report
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/upload_notification/list"
    And I wait for the page to be loaded
    And I change upload of the entry number "2" in the list to "no"
    And I change report of the entry number "3" in the list to "no"
    Then I should see the notifications table:
      | User     | User Email      | Upload | Report |
      | Catrobat | catro@catrob.at | yes    | yes    |
      | User1    | dog@catrob.at   | no     | no     |
      | User2    | dog2@catrob.at  | no     | no     |

  Scenario: Edit button should take me to edit page
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/upload_notification/list"
    And I wait for the page to be loaded
    And I click action button "edit" of the entry number "1"
    And I wait for the page to be loaded
    Then I should be on "/admin/upload_notification/1/edit"
    And I should see "User"
    And I should see "Email bei Upload"
    And I should see "Email bei Inappropriate Report"

  Scenario: Delete element from list
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/upload_notification/list"
    And I wait for the page to be loaded
    And I click action button "delete" of the entry number "1"
    And I wait for the page to be loaded
    And I click ".btn-danger"
    And I wait for the page to be loaded
    Then I should see the notifications table:
      | User     | User Email      | Upload | Report |
      | User1    | dog@catrob.at   | yes    | no     |
      | User2    | dog2@catrob.at  | no     | yes    |

  Scenario: Batch delete elements from list
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/upload_notification/list"
    And I wait for the page to be loaded
    And I check the batch action box of entry "1"
    And I check the batch action box of entry "3"
    And I click ".btn.btn-small.btn-primary"
    And I wait for the page to be loaded
    And I click ".btn-danger"
    And I wait for the page to be loaded
    Then I should see the notifications table:
      | User     | User Email      | Upload | Report |
      | User1    | dog@catrob.at   | yes    | no     |
