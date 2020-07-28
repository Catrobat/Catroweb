@web @translations
Feature: Users can choose between multiple languages. Text should be automatically translated.

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name    | owned by  |
      | 1  | Minions | Catrobat  |
      | 2  | Galaxy  | OtherUser |
      | 3  | Alone   | Catrobat  |
    And there are project reactions:
      | user      | project | type | created at       |
      | Catrobat  | 1       | 1    | 01.01.2017 12:00 |
      | Catrobat  | 2       | 2    | 01.01.2017 12:00 |
      | OtherUser | 1       | 4    | 01.01.2017 12:00 |

  Scenario: user can choose their language from a dropdown menu in the footer
    Given I am on homepage
    Then the selected language should be "English"
    And I wait for the page to be loaded
    And I should see "featured"
    But I should not see "Empfohlen"
    Then I switch the language to "Deutsch"
    And I wait for the page to be loaded
    Then the selected language should be "Deutsch"
    And I should see "Empfohlen"
    But I should not see "featured"

  Scenario: Users should be able to switch the language
    Given the selected language is "English"
    And I am on the homepage
    And I wait for the page to be loaded
    And I should see "Recommended projects"
    And the element "#recommended" should be visible
    And I should see a recommended homepage program having ID "1" and name "Minions"
    When I switch the language to "Russisch"
    And I wait for the page to be loaded
    Then I should see "ДОБРО ПОЖАЛОВАТЬ"
    And the element "#recommended" should be visible
    And I should see a recommended homepage program having ID "1" and name "Minions"
    When I switch the language to "French"
    And I wait for the page to be loaded
    Then I should see "LES PLUS TÉLÉCHARGÉS"
    And the element "#recommended" should be visible
    And I should see a recommended homepage program having ID "1" and name "Minions"

  Scenario: User with selected russian language sees the remix graph button and details on program page
    Given there are forward remix relations:
      | ancestor_id | descendant_id | depth |
      | 1           | 1             | 0     |
      | 1           | 2             | 1     |
      | 2           | 2             | 0     |
      | 3           | 3             | 0     |
    And I am on "/app/project/1"
    And the selected language is "English"
    And I wait for the page to be loaded
    Then I should see "REMIX GRAPH"
    And the element "#remix-graph-button-small" should be visible
    When I switch the language to "Russisch"
    And I wait for the page to be loaded
    Then I should see "Показать перемешанный граф"
    And the element "#remix-graph-button-small" should be visible

  Scenario: User with selected russian language sees the recommended programs that have been downloaded by other users on program page
    Given there are program download statistics:
      | id | program_id | downloaded_at       | ip             | country_code | country_name | user_agent | username  | referrer |
      | 1  | 1          | 2017-02-09 16:01:00 | 88.116.169.222 | AT           | Austria      | okhttp     | OtherUser | Facebook |
      | 2  | 3          | 2017-02-09 16:02:00 | 88.116.169.222 | AT           | Austria      | okhttp     | OtherUser | Facebook |
    And the selected language is "English"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then There should be recommended specific programs
    And the element "#specific-programs-recommendations" should be visible
    When I switch the language to "Russisch"
    And I wait for the page to be loaded
    Then There should be recommended specific programs
    And the element "#specific-programs-recommendations" should be visible