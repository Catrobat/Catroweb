@admin
Feature: Admin Report Notification
  In order to get informed of an upload
  As a subscribed admin
  I want to get an email when a program is uploaded or reported

  Background:
    Given there are admins:
      | name     | email           | id | password |
      | catroweb | admin@catrob.at | 1  | catroweb |
    Given there are users:
      | name     | email           | id | password |
      | Catrobat | catro@catrob.at | 2  | 123456   |
      | User1    | user1@catrob.at | 3  | 123456   |
      | User2    | user2@catrob.at | 4  | 123456   |
    And there are programs:
      | id | name      |
      | 1  | program 1 |
      | 2  | program 2 |
      | 3  | program 3 |

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

