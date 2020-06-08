@web @search
Feature: Searching for programs with tags

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User1    |
    And there are tags:
      | id | en        | de         |
      | 1  | Game      | Spiel      |
      | 2  | Animation | Animation  |
      | 3  | Story     | Geschichte |
    And there are projects:
      | id | name      | owned by | tags_id |
      | 1  | project 1 | Catrobat | 1,2     |
      | 2  | project 2 | Catrobat | 2       |
      | 3  | project 3 | User1    | 3       |

  Scenario: Searching other programs with the same tag
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I should see "project 1"
    And I should see "Game"
    And I should see "Animation"
    When I press on the tag "Animation"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    And I should see "project 1"
    And I should see "project 2"
    But I should not see "project 3"

  Scenario: search for programs should work
    When I am on "/app/search/Animation"
    And I wait for the page to be loaded
    Then I should see "Your search returned 2 results"
    And I should see "project 1"
    And I should see "project 2"
    But I should not see "project 3"
