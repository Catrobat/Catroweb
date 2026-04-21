@web @achievements
Feature: Owner should get bilingual achievement when projects have custom translations for two languages

  Background:
    Given I run the update achievements command
    And there are users:
      | name     |
      | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
      | 2  | project 2 | Catrobat |

  Scenario: Owner should get achievement when one project has custom translations for two languages
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    And I request "PUT" "/api/projects/1/translation/name/fr"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    And I request "PUT" "/api/projects/1/translation/description/es"
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"

  Scenario: Owner should get achievement when two projects each have a custom translations for a different language
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    And I request "PUT" "/api/projects/1/translation/description/fr"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    And I request "PUT" "/api/projects/2/translation/credit/es"
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Bilingual"

  Scenario: Owner should not get achievement when two projects have a custom translations for the same language
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    And I request "PUT" "/api/projects/1/translation/credit/fr"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    And I request "PUT" "/api/projects/2/translation/name/fr"
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Bilingual"
