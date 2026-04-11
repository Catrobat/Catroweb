@web @project_page
Feature: Project comments should be translatable via a button

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Alex     |
    And there are projects:
      | id | name     | owned by |
      | 1  | project1 | Catrobat |
    And there are comments:
      | id                                   | project_id | user_id | text |
      | 00000000-0000-0000-0000-000000000001 | 1          | 1       | c1   |
      | 00000000-0000-0000-0000-000000000002 | 1          | 2       | c2   |

  Scenario: Translate button should translate the corresponding comment
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#comment-translation-button-00000000-0000-0000-0000-000000000001" to be visible
    When I click "#comment-translation-button-00000000-0000-0000-0000-000000000001"
    And I wait for AJAX to finish
    Then the element "#remove-comment-translation-button-00000000-0000-0000-0000-000000000001" should be visible
    And the element "#comment-translation-wrapper-00000000-0000-0000-0000-000000000001" should be visible
    Then the "#comment-text-translation-00000000-0000-0000-0000-000000000001" element should contain "Fixed translation text"
    When I click "#remove-comment-translation-button-00000000-0000-0000-0000-000000000001"
    Then the element "#comment-translation-button-00000000-0000-0000-0000-000000000001" should be visible
    And the element "#comment-text-wrapper-00000000-0000-0000-0000-000000000001" should be visible
    And the "#comment-text-00000000-0000-0000-0000-000000000001" element should contain "c1"

  Scenario: Comment should only be translated by API once
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#comment-translation-button-00000000-0000-0000-0000-000000000002" to be visible
    When I click "#comment-translation-button-00000000-0000-0000-0000-000000000002"
    And I wait for AJAX to finish
    Then the element "#remove-comment-translation-button-00000000-0000-0000-0000-000000000002" should be visible
    And the element "#comment-translation-wrapper-00000000-0000-0000-0000-000000000002" should be visible
    And the "#comment-text-translation-00000000-0000-0000-0000-000000000002" element should contain "Fixed translation text"
    When I click "#remove-comment-translation-button-00000000-0000-0000-0000-000000000002"
    Then the element "#comment-translation-button-00000000-0000-0000-0000-000000000002" should be visible
    And the element "#comment-text-wrapper-00000000-0000-0000-0000-000000000002" should be visible
    When I click "#comment-translation-button-00000000-0000-0000-0000-000000000002"
    Then the element "#remove-comment-translation-button-00000000-0000-0000-0000-000000000002" should be visible
    And the element "#comment-translation-wrapper-00000000-0000-0000-0000-000000000002" should be visible
    And the "#comment-text-translation-00000000-0000-0000-0000-000000000002" element should contain "Fixed translation text"

  Scenario: Translation provider should have correct provider, source language, and target language
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-00000000-0000-0000-0000-000000000001"
    And I wait for AJAX to finish
    Then the element "#remove-comment-translation-button-00000000-0000-0000-0000-000000000001" should be visible
    And the element "#comment-translation-wrapper-00000000-0000-0000-0000-000000000001" should be visible
    Then the "#comment-translation-before-languages-00000000-0000-0000-0000-000000000001" element should contain "Translated by"
    Then the "#comment-translation-before-languages-00000000-0000-0000-0000-000000000001" element should contain "iTranslate"
    And the "#comment-translation-first-language-00000000-0000-0000-0000-000000000001" element should contain "English"
    And the "#comment-translation-between-languages-00000000-0000-0000-0000-000000000001" element should contain "to"
    And the "#comment-translation-second-language-00000000-0000-0000-0000-000000000001" element should contain "English"
    And the "#comment-translation-after-languages-00000000-0000-0000-0000-000000000001" element should contain ""

  Scenario: Translation button should not be visible for comments the user wrote
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element ".single-comment" to be visible
    Then the element "#comment-translation-button-00000000-0000-0000-0000-000000000001" should not exist
    And the element "#comment-translation-button-00000000-0000-0000-0000-000000000002" should be visible

  Scenario: Translation button should be visible for comments the user did not write
    Given I log in as "Alex"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-00000000-0000-0000-0000-000000000001" should be visible
    And the element "#comment-translation-button-00000000-0000-0000-0000-000000000002" should not exist

  Scenario: Translation button should be visible for comments when not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-00000000-0000-0000-0000-000000000001" should be visible
    And the element "#comment-translation-button-00000000-0000-0000-0000-000000000002" should be visible
