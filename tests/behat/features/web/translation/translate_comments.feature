@web @project_page
Feature: Project title, description and credits should be translatable via a button

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by |
      | 1  | project1 | Catrobat |
    And there are comments:
      | id | program_id | user_id | text |
      | 1  | 1          | 1       | c1   |
      | 2  | 1          | 1       | c2   |

  Scenario: Translate button should translate the corresponding comment
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translate-button-1" should exist
    Then the element "#comment-translate-button-2" should exist
    And the element "#comment-translate-button-1" should have a attribute "href" with value "https://translate.google.com/?q=c1"
    And the element "#comment-translate-button-2" should have a attribute "href" with value "https://translate.google.com/?q=c2"
