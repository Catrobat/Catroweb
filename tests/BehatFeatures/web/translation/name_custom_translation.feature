@web @project_page
Feature: Projects should have a name where a custom translation can be defined

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    And I wait 1000 milliseconds

  Scenario: Adding a custom name translation, then changing the language and keeping the unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 10000 milliseconds
    When I click "#edit-program-button"
    And I wait for AJAX to finish
    Then the element "#add-translation-button" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-name-text" with "This is a name translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-confirm"
    Then the "edit-name-text" field should contain "This is a name translation"
    And the "#edit-selected-language" element should contain "Russian"
    And the element "#edit-submit-button" should not be disabled

  Scenario: Adding a custom name translation, then changing the language while discarding changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 10000 milliseconds
    When I click "#edit-program-button"
    And I wait for AJAX to finish
    Then the element "#add-translation-button" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-name-text" with "This is a name translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-deny"
    And I wait for AJAX to finish
    Then the "edit-name-text" field should contain ""
    And the "#edit-selected-language" element should contain "Russian"
    And the element "#edit-submit-button" should be disabled

  Scenario: Adding a custom name translation, then changing the language but going back to unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 10000 milliseconds
    When I click "#edit-program-button"
    And I wait for AJAX to finish
    Then the element "#add-translation-button" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then I fill in "edit-name-text" with "This is a name translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-close"
    Then the "edit-name-text" field should contain "This is a name translation"
    And the "#edit-selected-language" element should contain "French"
    And the element "#edit-submit-button" should not be disabled
