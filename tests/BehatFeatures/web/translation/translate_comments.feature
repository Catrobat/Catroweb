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
      | id | project_id | user_id | text |
      | 1  | 1          | 1       | c1   |
      | 2  | 1          | 2       | c2   |

  Scenario: Translate button should translate the corresponding comment
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-1"
    And I wait for AJAX to finish
    Then the element "#remove-comment-translation-button-1" should be visible
    And the element "#comment-translation-wrapper-1" should be visible
    Then the "#comment-text-translation-1" element should contain "Fixed translation text"
    When I click "#remove-comment-translation-button-1"
    Then the element "#comment-translation-button-1" should be visible
    And the element "#comment-text-wrapper-1" should be visible
    And the "#comment-text-1" element should contain "c1"

  Scenario: Comment should only be translated by API once
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-2"
    And I wait for AJAX to finish
    Then the element "#remove-comment-translation-button-2" should be visible
    And the element "#comment-translation-wrapper-2" should be visible
    And the "#comment-text-translation-2" element should contain "Fixed translation text"
    When I click "#remove-comment-translation-button-2"
    Then the element "#comment-translation-button-2" should be visible
    And the element "#comment-text-wrapper-2" should be visible
    When I click "#comment-translation-button-2"
    Then the element "#remove-comment-translation-button-2" should be visible
    And the element "#comment-translation-wrapper-2" should be visible
    And the "#comment-text-translation-2" element should contain "Fixed translation text"

  Scenario: Translation provider should have correct provider, source language, and target language
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-1"
    And I wait for AJAX to finish
    Then the element "#remove-comment-translation-button-1" should be visible
    And the element "#comment-translation-wrapper-1" should be visible
    Then the "#comment-translation-before-languages-1" element should contain "Translated by"
    Then the "#comment-translation-before-languages-1" element should contain "iTranslate"
    And the "#comment-translation-first-language-1" element should contain "English"
    And the "#comment-translation-between-languages-1" element should contain "to"
    And the "#comment-translation-second-language-1" element should contain "English"
    And the "#comment-translation-after-languages-1" element should contain ""

  Scenario: Translation button should not be visible for comments the user wrote
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-1" should not exist
    And the element "#comment-translation-button-2" should be visible

  Scenario: Translation button should be visible for comments the user did not write
    Given I log in as "Alex"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-1" should be visible
    And the element "#comment-translation-button-2" should not exist

  Scenario: Translation button should be visible for comments when not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-1" should be visible
    And the element "#comment-translation-button-2" should be visible
