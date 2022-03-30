@web @achievements
Feature: Owner should get trilingual achievement when projects have custom translations for three languages

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

  Scenario: Owner should get achievement when one project has custom translations for three languages
    Given I POST login with user "Catrobat" and password "123456"
    And I request "PUT" "/app/translate/custom/project/1?field=name&language=fr&text=translated"
    And I request "PUT" "/app/translate/custom/project/1?field=description&language=es&text=translated"
    And I request "PUT" "/app/translate/custom/project/1?field=credit&language=it&text=translated"
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Trilingual"

  Scenario: Owner should get achievement when three projects each have a custom translations for a different language
    Given I POST login with user "Catrobat" and password "123456"
    And I request "PUT" "/app/translate/custom/project/1?field=description&language=fr&text=translated"
    And I request "PUT" "/app/translate/custom/project/2?field=credit&language=es&text=translated"
    And I request "PUT" "/app/translate/custom/project/3?field=name&language=de&text=translated"
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should contain "Trilingual"

  Scenario: Owner should not get achievement when three projects have a custom translations for the same language
    Given I POST login with user "Catrobat" and password "123456"
    And I request "PUT" "/app/translate/custom/project/1?field=credit&language=fr&text=translated"
    And I request "PUT" "/app/translate/custom/project/2?field=name&language=fr&text=translated"
    And I request "PUT" "/app/translate/custom/project/3?field=description&language=fr&text=translated"
    When I log in as "Catrobat"
    And I am on "/app/achievements"
    And I wait for the page to be loaded
    Then the "#unlocked-achievements" element should not contain "Trilingual"

