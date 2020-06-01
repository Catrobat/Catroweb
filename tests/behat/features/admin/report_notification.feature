@admin
Feature: Admin Report Notification
  In order to get informed of an upload
  As a subscribed admin
  I want to get an email when a program is uploaded or reported

  Background:
    Given there are admins:
      | name     | email           | id | password |
      | catroweb | admin@catrob.at | 0  | catroweb |
    Given there are users:
      | name     | email           | id | password |
      | Catrobat | catro@catrob.at | 1  | 123456   |
      | User1    | user1@catrob.at | 2  | 123456   |
      | User2    | user2@catrob.at | 3  | 123456   |
    And there are programs:
      | id | name      |
      | 1  | program 1 |
      | 2  | program 2 |
      | 3  | program 3 |
    And there are notifications:
      | user     | upload | report | summary |
      | catroweb | 0      | 0      | 0       |
      | Catrobat | 1      | 1      | 0       |
      | User1    | 1      | 0      | 0       |
      | User2    | 0      | 1      | 0       |

  Scenario: Users with access to the admin interface can subscribe to Reports and get notified
    Given I activate the Profiler
    And I use a valid JWT Bearer token for "Catrobat"
    And I report program 1 with category "spam" and note "Bad Program"
    Then I should see 2 outgoing emails
    And I should see a email with recipient "catro@catrob.at"
    And I should see a email with recipient "user2@catrob.at"

  Scenario: Users with access to the admin interface can subscribe to Uploads and get notified
    Given I activate the Profiler
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "checksum" with value "B472E2CB01AEACE0F359D0A1FE9A4036"
    And I have a valid Catrobat file, API version 2
    And I request "POST" "/api/projects"
    Then I should see 2 outgoing emails
    And I should see a email with recipient "catro@catrob.at"
    And I should see a email with recipient "user1@catrob.at"

  Scenario: Change upload and report
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/subscriptions/list"
    And I wait for the page to be loaded
    And I change upload of the entry number "2" in the list to "no"
    And I change report of the entry number "3" in the list to "no"
    Then I should see the notifications table:
      | User     | User Email      | Upload | Report |
      | catroweb | admin@catrob.at | no     | no     |
      | Catrobat | catro@catrob.at | yes    | yes    |
      | User1    | user1@catrob.at | no     | no     |
      | User2    | user2@catrob.at | no     | no     |

  Scenario: Edit button should take me to edit page
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/subscriptions/list"
    And I wait for the page to be loaded
    And I click action button "edit" of the entry number "1"
    And I wait for the page to be loaded
    Then I should be on "/admin/subscriptions/1/edit"
    And I should see "User"
    And I should see "Email bei Upload"
    And I should see "Email bei Inappropriate Report"

  Scenario: Delete element from list
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/subscriptions/list"
    And I wait for the page to be loaded
    And I click action button "delete" of the entry number "1"
    And I wait for the page to be loaded
    And I click ".btn-danger"
    And I wait for the page to be loaded
    Then I should see the notifications table:
      | User     | User Email      | Upload | Report |
      | Catrobat | catro@catrob.at | yes    | yes    |
      | User1    | user1@catrob.at | yes    | no     |
      | User2    | user2@catrob.at | no     | yes    |
    And I should not see "admin@catrob.at"

  Scenario: Batch delete elements from list
    When I log in as "catroweb" with the password "catroweb"
    And I am on "/admin/subscriptions/list"
    And I wait for the page to be loaded
    And I check the batch action box of entry "1"
    And I check the batch action box of entry "3"
    And I click ".btn.btn-small.btn-primary"
    And I wait for the page to be loaded
    And I click ".btn-danger"
    And I wait for the page to be loaded
    Then I should see the notifications table:
      | User     | User Email      | Upload | Report |
      | Catrobat | catro@catrob.at | yes    | yes    |
      | User2    | user2@catrob.at | no     | no     |
    And I should not see "admin@catrob.at"
    And I should not see "user1@catrob.at"

