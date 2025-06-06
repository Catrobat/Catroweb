@admin
Feature: Admin Preview Mail

  Background:
    Given there are admins:
      | name  | password | email                | id |
      | Admin | 123456   | admin@pocketcode.org | 1  |

    And there are users:
      | name     | password | email               | id |
      | Tyrell   | 123456   | dev1@pocketcode.org | 2  |


Scenario: Valid Confirmation Email Template:
  Given I log in as "Admin" with the password "123456"
  And I am on "/admin/user-communication/email/list"
  And I wait for the page to be loaded
  When I select option 1 from the dropdown "template-select"
  And I enter "Tyrell" into the "username" field
  And I click "#preview-button"
  And I switch to the new tab
  And I wait for the page to be loaded
  Then I should see "Catrobat"

Scenario: Valid Reset Email Template:
  Given I log in as "Admin" with the password "123456"
  And I am on "/admin/user-communication/email/list"
  And I wait for the page to be loaded
  When I select option 2 from the dropdown "template-select"
  And I enter "Tyrell" into the "username" field
  And I click "#preview-button"
  And I switch to the new tab
  And I wait for the page to be loaded
  Then I should see "Catrobat"

Scenario: Valid Simple Message Email Template:
  Given I log in as "Admin" with the password "123456"
  And I am on "/admin/user-communication/email/list"
  And I wait for the page to be loaded
  When I select option 3 from the dropdown "template-select"
  And I enter "Tyrell" into the "username" field
  And I enter "Subject" into the "subject" field
  And I enter "Content" into the "content" field
  And I click "#preview-button"
  And I switch to the new tab
  And I wait for the page to be loaded
  Then I should see "Catrobat"

Scenario: Invalid Subject Simple Message Email Template:
  Given I log in as "Admin" with the password "123456"
  And I am on "/admin/user-communication/email/list"
  And I wait for the page to be loaded
  When I select option 3 from the dropdown "template-select"
  And I enter "Tyrell" into the "username" field
  And I click "#preview-button"
  And I switch to the new tab
  And I wait for the page to be loaded
  Then I should see "Empty subject!"

Scenario: Invalid Content Simple Message Email Template:
  Given I log in as "Admin" with the password "123456"
  And I am on "/admin/user-communication/email/list"
  And I wait for the page to be loaded
  When I select option 3 from the dropdown "template-select"
  And I enter "Tyrell" into the "username" field
  And I enter "Subject" into the "subject" field
  And I click "#preview-button"
  And I switch to the new tab
  And I wait for the page to be loaded
  Then I should see "Empty message!"
