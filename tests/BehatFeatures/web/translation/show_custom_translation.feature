@web @project_page
Feature: Projects should show a previously defined custom translation for user's language

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
    And there are projects:
      | id | name      | owned by  | description    | credit     |
      | 1  | project 1 | Catrobat  | my description | my credits |
    And there are project custom translations:
      | project_id | language | name            | description            | credit             |
      | 1          | de       | translated name | translated description | translated credits |

  Scenario: Viewing a previously entered custom description and credits translation
    Given I am on "/app/project/1"
    Then the selected language should be "English"
    And I wait for the page to be loaded
    And I should see "my description"
    And I should see "my credits"
    But I should not see "translated description"
    And I should not see "translated credits"
    Then I switch the language to "Deutsch"
    And I wait for the page to be loaded
    Then the selected language should be "Deutsch"
    And I should see "translated description"
    And I should see "translated credits"
    But I should not see "my description"
    And I should not see "my credits"

  Scenario: Should not see custom description and credits when not defined
    Given I am on "/app/project/1"
    Then the selected language should be "English"
    And I wait for the page to be loaded
    And I should see "my description"
    And I should see "my credits"
    Then I switch the language to "French"
    And I wait for the page to be loaded
    Then the selected language should be "French"
    And I should see "my description"
    And I should see "my credits"
