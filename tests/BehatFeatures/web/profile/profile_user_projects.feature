@web @profile_page @recommendations
Feature: There should be all projects of a user presented on a profile page

  Background:
    Given there are users:
      | id | name  |
      | 1  | User1 |
      | 2  | User2 |
      | 3  | User3 |
    And there are projects:
      | id | name       | owned by |
      | 1  | project 1  | User1    |
      | 2  | project 2  | User1    |
      | 3  | project 3  | User2    |
      | 4  | project 4  | User3    |
      | 5  | project 5  | User3    |
      | 6  | project 6  | User3    |
      | 7  | project 7  | User3    |
      | 8  | project 8  | User3    |
      | 9  | project 9  | User3    |
      | 10 | project 10 | User3    |
      | 11 | project 11 | User3    |
      | 12 | project 12 | User3    |
      | 13 | project 13 | User3    |
      | 14 | project 14 | User3    |
      | 15 | project 15 | User3    |
      | 16 | project 16 | User3    |
      | 17 | project 17 | User3    |
      | 18 | project 18 | User3    |
      | 19 | project 19 | User3    |
      | 20 | project 20 | User3    |
      | 21 | project 21 | User3    |
      | 22 | project 22 | User3    |
      | 23 | project 23 | User3    |
      | 24 | project 24 | User3    |
      | 25 | project 25 | User3    |
      | 26 | project 26 | User3    |
      | 27 | project 27 | User3    |
      | 28 | project 28 | User3    |

  Scenario: Show User1's public profile
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    Then I should see "User1"
    And I should see "2 projects"
    And I should see "Projects"
    And I should see "project 1"
    And I should see "project 2"
    But I should not see "User2"
    And I should not see "project 3"

  Scenario: Show User2's profile
    Given I am on "/app/user/2"
    And I wait for the page to be loaded
    Then I should see "User2"
    And I should see "1 projects"
    And I should see "Projects"
    And I should see "project 3"
    But I should not see "User1"
    And I should not see "project 1"
    And I should not see "project 2"

  Scenario: projects should be ordered newest first
    Given I log in as "User1"
    And I am on "/app/user"
    And I wait for the page to be loaded
    When I click ".own-project-list__project"
    And I wait for AJAX to finish
    Then I am on "/app/project/28"

  Scenario: at a profile page there should always all projects be visible
    Given I am on "/app/user/3"
    And I wait for the page to be loaded
    Then I should see "project 4"
    And I should see "project 5"
    And I should see "project 6"
    And I should see "project 7"
    And I should see "project 8"
    And I should see "project 9"
    And I should see "project 10"
    And I should see "project 11"
    And I should see "project 12"
    And I should see "project 13"
    And I should see "project 14"
    And I should see "project 15"
    And I should see "project 16"
    And I should see "project 17"
    And I should see "project 18"
    And I should see "project 19"
    And I should see "project 20"
    And I should see "project 21"
    And I should see "project 22"
    And I should see "project 23"
    And I should see "project 24"
    And I should see "project 25"
    And I should see "project 26"
    And I should see "project 27"
    And I should see "project 28"

  Scenario: at my profile page there should always all projects be visible up to 20 until i scroll to the end of the screen
    Given I log in as "User3"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see 20 ".own-project-list__project"
    When I scroll vertical on "own-projects" using a value of "30"
    And I wait for AJAX to finish
    Then I should see 5 ".own-project-list__project"

  Scenario: at a profile page there should always all projects be visible
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    Then I should see 2 "#user-projects .project-list__project"
    But the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist

  Scenario: at my profile page there should always all projects be visible
    Given I log in as "User3"
    And I am on "/app/user"
    And I wait for the page to be loaded
    Then I should see 20 ".own-project-list__project"
    But the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist

  Scenario: at a profile page there should always all projects be visible
    Given I am on "/app/user/3"
    And I wait for the page to be loaded
    Then I should see 25 "#user-projects .project-list__project"
    But the element ".button-show-more" should not exist
    And the element ".button-show-less" should not exist

