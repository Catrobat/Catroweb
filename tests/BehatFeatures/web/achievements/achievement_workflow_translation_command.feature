@web @achievements
Feature: AchievementWorkflow_Translation_Command should retroactively unlock translation achievements

  Background:
    Given I run the update achievements command
    And there are users:
      | name     |
      | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |
      | 3  | project 3 | Catrobat |
      | 4  | project 4 | Catrobat |
      | 5  | project 5 | Catrobat |

  Scenario: Owner should get bilingual achievement when one project has custom translations for two languages
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 1          | fr       | name2 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"
    And the "#unlocked-achievements" element should not contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

  Scenario: Owner should get bilingual achievement when two projects each have a custom translations for a different language
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 2          | fr       | name2 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"
    And the "#unlocked-achievements" element should not contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

  Scenario: Owner should not get achievement when two projects have a custom translations for the same language
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 2          | en       | name2 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Bilingual"
    And the "#unlocked-achievements" element should not contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

  Scenario: Owner should get bilingual and trilingual achievements when one project has custom translations for three languages
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 1          | fr       | name2 |             |        |
      | 1          | it       | name3 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"
    And the "#unlocked-achievements" element should contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

  Scenario: Owner should get bilingual and trilingual achievements when three projects each have a custom translations for a different language
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 2          | fr       | name2 |             |        |
      | 3          | it       | name3 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"
    And the "#unlocked-achievements" element should contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

  Scenario: Owner should not get achievement when three projects have a custom translations for the same language
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 2          | en       | name2 |             |        |
      | 3          | en       | name3 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Bilingual"
    And the "#unlocked-achievements" element should not contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

  Scenario: Owner should get bilingual, trilingual and linguist achievements when one project has custom translations for five languages
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 1          | fr       | name2 |             |        |
      | 1          | it       | name3 |             |        |
      | 1          | de       | name4 |             |        |
      | 1          | ar       | name5 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"
    And the "#unlocked-achievements" element should contain "Trilingual"
    And the "#unlocked-achievements" element should contain "Linguist"

  Scenario: Owner should get bilingual, trilingual and linguist achievements when five projects each have a custom translations for a different language
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 2          | fr       | name2 |             |        |
      | 3          | it       | name3 |             |        |
      | 4          | de       | name4 |             |        |
      | 5          | ar       | name5 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"
    And the "#unlocked-achievements" element should contain "Trilingual"
    And the "#unlocked-achievements" element should contain "Linguist"

  Scenario: Owner should not get achievement when five projects have a custom translations for the same language
    Given there are project custom translations:
      | project_id | language | name  | description | credit |
      | 1          | en       | name1 |             |        |
      | 2          | en       | name2 |             |        |
      | 3          | en       | name3 |             |        |
      | 4          | en       | name4 |             |        |
      | 5          | en       | name5 |             |        |
    And I run the add translation user achievements command
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Bilingual"
    And the "#unlocked-achievements" element should not contain "Trilingual"
    And the "#unlocked-achievements" element should not contain "Linguist"

