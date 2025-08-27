@web @search
Feature: Searching for projects with extensions

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are extensions:
      | id | internal_title | title_ltm_code |
      | 1  | arduino        | __arduino      |
      | 2  | drone          | __drone        |
      | 3  | mindstorms     | __mindstorms   |
      | 4  | phiro          | __phiro        |
      | 5  | raspberry_pi   | __raspberry    |
    And there are projects:
      | id | name      | owned by | extensions       |
      | 1  | project 1 | Catrobat | mindstorms,phiro |
      | 2  | project 2 | Catrobat | mindstorms,drone |
      | 3  | project 3 | User1    | drone            |
    And I wait 500 milliseconds

  Scenario: Searching other projects with the same extensions
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I should see "project 1"
    And I should see "__mindstorms"
    And I should see "__phiro"
    When I press on the extension "__mindstorms"
    And I wait for the page to be loaded
    Then I should see "Search results"
    Then I should see "project 1"
    And I should see "project 2"
    And I should not see "project 3"
