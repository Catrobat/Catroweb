@web @search
Feature: Searching for projects with tags

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are tags:
      | internal_title | title_ltm_code |
      | game           | __Game         |
      | animation      | __Animation    |
      | story          | __Story        |
    And there are projects:
      | id | name      | owned by | tags            |
      | 1  | project 1 | Catrobat | game, animation |
      | 2  | project 2 | Catrobat | animation       |
      | 3  | project 3 | User1    | story           |
    And I wait 1000 milliseconds

  Scenario: Searching other projects with the same tag
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I should see "project 1"
    And I should see "__Game"
    And I should see "__Animation"
    When I press on the tag "__Animation"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    And I should see "project 1"
    And I should see "project 2"
    But I should not see "project 3"

  Scenario: search for tags should work
    When I am on "/app/search_old/Animation"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    And I should see "project 1"
    And I should see "project 2"
    But I should not see "project 3"
