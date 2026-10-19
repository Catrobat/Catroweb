@web
Feature: Leaderboards on the homepage
  In order to compete with other creators
  As a visitor
  I want to see the top creators of all time and of this week on the homepage

  Background:
    Given there are users:
      | id | name  |
      | 1  | Alice |
      | 2  | Bob   |
    And there are projects:
      | id | name      | owned by | downloads |
      | 1  | Alice one | Alice    | 10        |
      | 2  | Bob one   | Bob      | 50        |
    And there are project downloads:
      | project   | downloaded by | days ago |
      | Alice one | Bob           | 1        |
    And I run the update user ranking command

  Scenario: Both leaderboards are shown with their top creators
    Given I am on homepage
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then one of the ".project-list__title" elements should contain "Top creators"
    And one of the ".project-list__title" elements should contain "Top creators this week"
    And I wait for the element "#home-users__rank" to contain "Bob"
    And the "#home-users__rank" element should contain "Alice"
    And I wait for the element "#home-users__rank_recent" to contain "Alice"
    And the element "#home-users__rank_recent" should not contain "Bob"
