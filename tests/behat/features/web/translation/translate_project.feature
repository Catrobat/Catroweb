@web @project_page
Feature: Project title, description and credits should be translatable via a button

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription |          |
      | 2  | project2 | Catrobat |               | mycredit |
      | 3  | project3 | Catrobat |               |          |

  Scenario: Translate button should translate title and description when available
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#translate-program" should exist
    And the element "#translate-program" should have a attribute "href" with value "https://translate.google.com/?q="
    And the element "#translate-program" should have a attribute "href" with value "project1"
    And the element "#translate-program" should have a attribute "href" with value "mydescription"

  Scenario: Translate button should translate title and credit when available
    Given I am on "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#translate-program" should exist
    And the element "#translate-program" should have a attribute "href" with value "https://translate.google.com/?q="
    And the element "#translate-program" should have a attribute "href" with value "project2"
    And the element "#translate-program" should have a attribute "href" with value "mycredit"

  Scenario: Translate button should translate only title when credit and descriptions are not available
    Given I am on "/app/project/3"
    And I wait for the page to be loaded
    Then the element "#translate-program" should exist
    And the element "#translate-program" should have a attribute "href" with value "https://translate.google.com/?q="
    And the element "#translate-program" should have a attribute "href" with value "project3"
