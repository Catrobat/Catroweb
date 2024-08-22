@web @project_page
Feature: Projects should have an editor a custom translation can be defined

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by | description    | credit     |
      | 1  | project 1 | Catrobat | my description | my credits |

  Scenario: Custom translation editor should be visible
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-navigation" should be visible
    And the element "#edit-default-button" should be visible
    And the element "#add-translation-button" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then the element "#edit-name-text" should be visible
    And the element "#edit-description-text" should be visible
    And the element "#edit-credits-text" should be visible
    And the element "#edit-text-ui" should be visible
    And the element "#edit-language-selector" should be visible
    And the element "#edit-submit-button" should be visible
    And the element "#edit-delete-button" should not be visible
    And the element "#edit-submit-button" should be disabled

  Scenario: Special language variants should be included in "add translation" language list
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    And I click "#add-translation-button"
    And I wait for AJAX to finish
    And I click "#edit-language-selector"
    Then I should see "Chinese (China)"
    And I should see "Chinese (Taiwan)"
    And I should see "Portuguese (Brazil)"
    And I should see "Portuguese (Portugal)"

  Scenario: Already defined language should not be included in "add translation" language list
    Given there are project custom translations:
      | project_id | language | name             | description | credit |
      | 1          | fr       | name translation |             |        |
    And I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    And I click "#add-translation-button"
    And I wait for AJAX to finish
    And I click "#edit-language-selector"
    Then I should see "Default"
    Then I should see "French"

  Scenario: Adding a custom translation
    Given I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-navigation" should be visible
    When I click "#add-translation-button"
    And I wait for AJAX to finish
    Then I choose "French" from selector "#edit-language-selector"
    And I wait for AJAX to finish
    Then the element "#edit-submit-button" should be disabled
    When I fill in "edit-name-text" with "This is a name translation"
    And I fill in "edit-description-text" with "This is a description translation"
    And I fill in "edit-credits-text" with "This is a credit translation"
    Then the element "#edit-submit-button" should not be disabled
    When I click "#edit-submit-button"
    And I wait for AJAX to finish
    And I wait 500 milliseconds
    Then the element "#edit-text-navigation" should be visible
    And the element "#edit-fr-button" should exist
    And I should see "French"

  Scenario: Viewing a custom translation
    Given there are project custom translations:
      | project_id | language | name             | description             | credit             |
      | 1          | fr       | name translation | description translation | credit translation |
    And I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-navigation" should be visible
    And the element "#edit-fr-button" should be visible
    And I should see "French"
    When I click "#edit-fr-button"
    And I wait for AJAX to finish
    Then the element "#edit-text-ui" should be visible
    And the element "#edit-name-text" should be visible
    And the element "#edit-description-text" should be visible
    And the element "#edit-credits-text" should be visible
    And the element "#edit-submit-button" should be visible
    And the element "#edit-delete-button" should be visible
    And the element "#edit-language-selector" should not be visible
    And the element "#edit-submit-button" should be disabled
    And the "edit-name-text" field should contain "name translation"
    And the "edit-description-text" field should contain "description translation"
    And the "edit-credits-text" field should contain "credit translation"

  Scenario: Delete a custom translation with the delete button
    Given there are project custom translations:
      | project_id | language | name             | description             | credit             |
      | 1          | fr       | name translation | description translation | credit translation |
    And I log in as "Catrobat"
    And I go to "/app/project/1"
    And I wait for the page to be loaded
    And I wait 500 milliseconds
    When I click "#edit-project-button"
    And I wait for AJAX to finish
    Then the element "#edit-fr-button" should be visible
    When I click "#edit-fr-button"
    And I wait for AJAX to finish
    When I click "#edit-delete-button"
    Then should see "Are you sure you want to delete the translation?"
    When I click ".swal2-deny"
    And I wait for AJAX to finish
    Then there should be project custom translations:
      | project_id | language | name | description | credit |
