@web
Feature: Persist project and comment translation

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description | credit |
      | 1  | project1 | Catrobat |             |        |
      | 2  | project2 | Catrobat |             |        |
    And there are comments:
      | id | program_id | user_id | text |
      | 1  | 2          | 1       | c1   |
      | 2  | 1          | 1       | c2   |

  Scenario: Create new entry the first time a program is translated
    Given there are project machine translations:
      | project_id | source_language | target_language | provider | usage_count |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#program-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 1           |

  Scenario: Increment usage count if program entry already exists
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 2           |
      | 2          | en              | fr-FR           | itranslate | 1           |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#program-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 3           |
      | 2          | en              | fr-FR           | itranslate | 1           |

  Scenario: Create new entry the first time a comment is translated
    Given there are comment machine translations:
      | comment_id | source_language | target_language | provider | usage_count |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-2"
    And I wait for AJAX to finish
    Then there should be comment machine translations:
      | comment_id | source_language | target_language | provider   | usage_count |
      | 2          | en              | fr-FR           | itranslate | 1           |

  Scenario: Increment usage count if comment entry already exists
    Given there are comment machine translations:
      | comment_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 1           |
      | 2          | en              | fr-FR           | itranslate | 1           |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-2"
    And I wait for AJAX to finish
    Then there should be comment machine translations:
      | comment_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 1           |
      | 2          | en              | fr-FR           | itranslate | 2           |
