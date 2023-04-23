@admin
Feature: Admin Email Examples
  It should be possible to send custom mails to users.

  Background:
    Given there are admins:
      | name     |
      | Adminius |

    And there are users:
      | name      | password | token      | email               | id |
      | Superman  | 123456   | cccccccccc | dev1@pocketcode.org | 2  |

  Scenario: Send a custom email to user:
    Given I log in as "Adminius" with the password "123456"
    And I am on "admin/mail/list"
    And I wait for the page to be loaded
    Then I should see "User Name:"
    And I should see "Subject:"
    And I should see "Titel:"
    And I should see "Content:"
    And I should see "Submit:"
    And I should see "Result:"
    Then I click ".btn"
    Then I should see "User does not exist"