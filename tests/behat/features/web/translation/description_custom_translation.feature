@web @project_page
Feature: Projects should have descriptions where a custom translation can be defined

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
    And there are projects:
      | id | name      | owned by  | description    |
      | 1  | project 1 | Catrobat  | my description |

  Scenario: Adding and viewing a custom description translation
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should be visible
    When I click "#edit-description-button"
    And I wait for AJAX to finish
    Then the element "#description" should not be visible
    But the element "#edit-description" should be visible
    And the element "#description-language-selector" should be visible
    And the element "#edit-description-submit-button" should be visible
    Then I choose "French" from selector "#description-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-description" with "This is a description translation"
    And I click "#edit-description-submit-button"
    And I wait for AJAX to finish
    Then the element "#description" should be visible
    And the element "#edit-description-ui" should not be visible
    And I should see "my description"
    When I click "#edit-description-button"
    And I wait for AJAX to finish
    Then the element "#edit-description" should be visible
    And the element "#description-language-selector" should be visible
    Then I choose "French" from selector "#description-language-selector"
    And I wait for AJAX to finish
    Then the "edit-description" field should contain "This is a description translation"

  Scenario: Editing custom translation, then changing the language and keeping the unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should be visible
    When I click "#edit-description-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#description-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-description" with "This is a description translation"
    Then I choose "Russian" from selector "#description-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-confirm"
    Then the "edit-description" field should contain "This is a description translation"
    And I should see "Russian"

  Scenario: Editing custom translation, then changing the language while discarding changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should be visible
    When I click "#edit-description-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#description-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-description" with "This is a description translation"
    Then I choose "Russian" from selector "#description-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-deny"
    And I wait for AJAX to finish
    Then the "edit-description" field should contain ""
    And I should see "Russian"

  Scenario: Editing custom translation, then changing the language but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#edit-description-button" should be visible
    When I click "#edit-description-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#description-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-description" with "This is a description translation"
    Then I choose "Russian" from selector "#description-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-close"
    Then the "edit-description" field should contain "This is a description translation"
    And I should see "French"
