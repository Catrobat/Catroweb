@web @project_page
Feature: Projects should have descriptions where a custom translation can be defined

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | description    |
      | 1  | project 1 | Catrobat | my description |
      | 2  | project 2 | Catrobat |                |
    And I wait 1000 milliseconds

  Scenario: Adding a custom description translation, then changing the language and keeping the unsaved changes
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
    Then I fill in "edit-description-text" with "This is a description translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-confirm"
    Then the "edit-description-text" field should contain "This is a description translation"
    And the "#edit-selected-language" element should contain "Russian"

  Scenario: Adding a custom description translation, then changing the language while discarding changes
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
    Then I fill in "edit-description-text" with "This is a description translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-deny"
    And I wait for AJAX to finish
    Then the "edit-description-text" field should contain ""
    And the "#edit-selected-language" element should contain "Russian"

  Scenario: Adding a custom description translation, then changing the language but going back to unsaved changes
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
    Then I fill in "edit-description-text" with "This is a description translation"
    Then I choose "Russian" from selector "#edit-language-selector"
    And I should see "Would you like to keep your current changes?"
    When I click ".swal2-close"
    And I wait for AJAX to finish
    Then the "edit-description-text" field should contain "This is a description translation"
    And the "#edit-selected-language" element should contain "French"

  Scenario: Description text field should be disabled if there is not a default description defined
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    And I wait 10000 milliseconds
    When I click "#edit-program-button"
    And I wait for AJAX to finish
    Then the element "#add-translation-button" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should not be disabled
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be disabled

  Scenario: Adding a default description, then changing the language without saving and keeping the unsaved changes
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    And I wait 10000 milliseconds
    When I click "#edit-program-button"
    And I wait for AJAX to finish
    Then the element "#add-translation-button" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then I fill in "edit-description-text" with "This is a default description"
    When I choose "French" from selector "#edit-language-selector"
    Then I should see "Would you like to keep your current changes?"
    When I click ".swal2-confirm"
    And I wait for AJAX to finish
    Then the element "#edit-description-text" should be disabled
    And I should see "No description available."