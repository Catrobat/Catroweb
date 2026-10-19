@web @project_browse
Feature: Not-for-kids project thumbnails are blurred in project lists and search results

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | User2    |
    And there are projects:
      | id | name          | owned by | not_for_kids |
      | 1  | safe project  | Catrobat | 0            |
      | 2  | adult project | User2    | 1            |
    And I wait 500 milliseconds

  Scenario: Explore section blurs not-for-kids thumbnails
    Given I am on "/app/projects"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "adult project"
    And the element ".projects-list-item-wrapper[data-project-id='2'] img[style*='blur']" should exist
    And the element ".projects-list-item-wrapper[data-project-id='1'] img[style*='blur']" should not exist

  Scenario: Search results blur not-for-kids thumbnails
    Given I am on "/app/search/project"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    Then I should see "adult project"
    And the element ".search-card[href$='/project/2'] img[style*='blur']" should exist
    And the element ".search-card[href$='/project/1'] img[style*='blur']" should not exist
