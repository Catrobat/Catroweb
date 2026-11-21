@web @profile_page
Feature: A profile loads its projects page by page while scrolling

  Background:
    Given there are users:
      | id | name  |
      | 1  | Pager |
    And there are projects:
      | id | name | owned by |
      | 1 | paged project 1 | Pager |
      | 2 | paged project 2 | Pager |
      | 3 | paged project 3 | Pager |
      | 4 | paged project 4 | Pager |
      | 5 | paged project 5 | Pager |
      | 6 | paged project 6 | Pager |
      | 7 | paged project 7 | Pager |
      | 8 | paged project 8 | Pager |
      | 9 | paged project 9 | Pager |
      | 10 | paged project 10 | Pager |
      | 11 | paged project 11 | Pager |
      | 12 | paged project 12 | Pager |
      | 13 | paged project 13 | Pager |
      | 14 | paged project 14 | Pager |
      | 15 | paged project 15 | Pager |
      | 16 | paged project 16 | Pager |
      | 17 | paged project 17 | Pager |
      | 18 | paged project 18 | Pager |
      | 19 | paged project 19 | Pager |
      | 20 | paged project 20 | Pager |
      | 21 | paged project 21 | Pager |
      | 22 | paged project 22 | Pager |
      | 23 | paged project 23 | Pager |
      | 24 | paged project 24 | Pager |
      | 25 | paged project 25 | Pager |
      | 26 | paged project 26 | Pager |
      | 27 | paged project 27 | Pager |
      | 28 | paged project 28 | Pager |
      | 29 | paged project 29 | Pager |
      | 30 | paged project 30 | Pager |
      | 31 | paged project 31 | Pager |
      | 32 | paged project 32 | Pager |
      | 33 | paged project 33 | Pager |
      | 34 | paged project 34 | Pager |
      | 35 | paged project 35 | Pager |

  Scenario: All projects appear after scrolling past the first page of 30
    Given I am on "/app/user/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    When I scroll to the bottom of the page
    And I wait for AJAX to finish
    And I scroll to the bottom of the page
    And I wait for AJAX to finish
    Then I should see 35 "#user-projects .project-list__project"
