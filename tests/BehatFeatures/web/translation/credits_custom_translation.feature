@web @project_page
Feature: Projects should have credits where a custom translation can be defined

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | credit     |
      | 1  | project 1 | Catrobat | my credits |

  Scenario: Custom translation editor should be visible
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then the element "#credits" should not be visible
    But the element "#edit-text" should be visible
    And the element "#edit-text-ui" should be visible
    And the element "#edit-language-selector" should be visible
    And the element "#edit-submit-button" should be visible

  Scenario: Adding a custom credits translation
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a credit translation"
    And I click "#edit-submit-button"
    And I wait for AJAX to finish
    And I should see "my credits"

  Scenario: Viewing a custom description translation
    Given there are project custom translations:
      | project_id | language | name | description | credit                       |
      | 1          | fr       |      |             | This is a credit translation |
    And I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then the element "#edit-text" should be visible
    And the element "#edit-language-selector" should be visible
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then the "edit-text" field should contain "This is a credit translation"

  Scenario: Editing custom translation, then changing the language and keeping the unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a credit translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-confirm"
    Then the "edit-text" field should contain "This is a credit translation"
    And I should see "Russian"

  Scenario: Editing custom translation, then changing the language while discarding changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a credit translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-deny"
    And I wait for AJAX to finish
    Then the "edit-text" field should contain ""
    And I should see "Russian"

  Scenario: Editing custom translation, then changing the language but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    When I click "#edit-credits-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-text" with "This is a credit translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-close"
    And I wait for AJAX to finish
    Then the "edit-text" field should contain "This is a credit translation"
    And I should see "French"
