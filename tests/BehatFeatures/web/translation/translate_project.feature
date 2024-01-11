@web @project_page
Feature: Project title, description and credits should be translatable via a button

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Alex     |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat |               |          |
      | 2  | project2 | Catrobat | mydescription |          |
      | 3  | project3 | Catrobat |               | mycredit |
      | 4  | project4 | Catrobat | mydescription | mycredit |

  Scenario: Translate button should translate only title when description and credits not available
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "translated project1"
    And the element "#description" should be visible
    And the "#description" element should contain "No description available."
    And the element "#credits" should be visible
    And the "#credits" element should contain "No notes and credits available."
    When I click "#remove-project-translation-button"
    Then the element "#project-translation-button" should be visible
    And the element "#name" should be visible
    And the "#name" element should contain "project1"

  Scenario: Translate button should translate title and description when available
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "translated project2"
    And the element "#description-translation" should be visible
    And the "#description-translation" element should contain "translated mydescription"
    When I click "#remove-project-translation-button"
    Then the element "#project-translation-button" should be visible
    And the element "#name" should be visible
    And the element "#description" should be visible

  Scenario: Translate button should translate title and credit when available
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "translated project3"
    And the element "#credits-translation" should be visible
    And the "#credits-translation" element should contain "translated mycredit"
    When I click "#remove-project-translation-button"
    Then the element "#project-translation-button" should be visible
    And the element "#name" should be visible
    And the element "#credits" should be visible

  Scenario: Translate button should translate title, description, and credit when available
    Given I am on "/app/project/4"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "translated project4"
    And the element "#description-translation" should be visible
    And the "#description-translation" element should contain "translated mydescription"
    And the element "#credits-translation" should be visible
    And the "#credits-translation" element should contain "translated mycredit"
    When I click "#remove-project-translation-button"
    Then the element "#project-translation-button" should be visible
    And the element "#name" should be visible
    And the element "#description" should be visible
    And the element "#credits" should be visible

  Scenario: Translate button should translate title, description, and credit only once
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    When I click "#remove-project-translation-button"
    Then the element "#project-translation-button" should be visible
    And the element "#name" should be visible
    When I click "#project-translation-button"
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible

  Scenario: Translation should show translated by line
    Given I am on "/app/project/4"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#credits-translation-wrapper" should be visible
    And the "#project-translation-before-languages" element should contain "Translated by"
    And the "#project-translation-before-languages" element should contain "iTranslate"
    And the "#project-translation-first-language" element should contain "English"
    And the "#project-translation-between-languages" element should contain "to"
    And the "#project-translation-second-language" element should contain "English"
    And the "#project-translation-after-languages" element should contain ""

  Scenario: Translation button should not be visible for projects the user created
    Given I log in as "Catrobat"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#project-translation-button" should not exist

  Scenario: Translation button should be visible for projects the user did not create
    Given I log in as "Alex"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#project-translation-button" should be visible

  Scenario: Translation button should be visible for projects when not logged in
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#project-translation-button" should be visible

  Scenario: Use cached translation for title when description and credits not available
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name     | cached_description | cached_credits |
      | 1          | ru              | en              | itranslate | 16          | cached project1 |                    |                |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "cached project1"
    And the element "#description" should be visible
    And the "#description" element should contain "No description available."
    And the element "#credits" should be visible
    And the "#credits" element should contain "No notes and credits available."

  Scenario: Use cached translation for title and description when available
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name     | cached_description  | cached_credits |
      | 2          | ru              | en              | itranslate | 16          | cached project2 | cached description2 |                |
    And I am on "/app/project/2"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "cached project2"
    And the element "#description-translation" should be visible
    And the "#description-translation" element should contain "cached description2"

  Scenario: Use cached translation for title and credit when available
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name     | cached_description | cached_credits |
      | 3          | ru              | en              | itranslate | 16          | cached project3 |                    | cached credit3 |
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "cached project3"
    And the element "#credits-translation" should be visible
    And the "#credits-translation" element should contain "cached credit3"

  Scenario: Use cached translation for ttitle, description, and credit when available
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name     | cached_description  | cached_credits |
      | 4          | ru              | en              | itranslate | 16          | cached project4 | cached description4 | cached credit4 |
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the element "#remove-project-translation-button" should be visible
    And the element "#name-translation" should be visible
    And the "#name-translation" element should contain "cached project4"
    And the element "#description-translation" should be visible
    And the "#description-translation" element should contain "cached description4"
    And the element "#credits-translation" should be visible
    And the "#credits-translation" element should contain "cached credit4"
