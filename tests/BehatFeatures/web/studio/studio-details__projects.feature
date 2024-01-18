@web @studio
Feature: A studio has a project section



  Background:
    And there are users:
      | id | name        |
      | 1  | StudioAdmin |

    And there are studios:
      | id | name             | description     | allow_comments | is_public |
      | 1  | CatrobatStudio01 | hasADescription | true           | true      |
      | 2  | CatrobatStudio03 | hasADescription | true           | false     |
      | 3  | CatrobatStudio04 | hasADescription | true           | false     |
      | 4  | CatrobatStudio05 | hasADescription | true           | false     |
    And there are projects:
      | id | name      | description    | owned by | apk_ready |
      | 1  | project 1 | my description | StudioAdmin | true      |
      | 2  | project 2 | my description | StudioAdmin | true      |

    And there are studio users:
      | id | user          | studio_id | role   |
      | 1  | StudioAdmin   | 1         | admin  |

    And there are studio projects:
      | id  | studio_id | project      | user   |
      | 1   | 1         | project 2    | StudioAdmin  |


  Scenario: User clicks studio add button
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#show-add-studio-project-button"
    And I wait 500 milliseconds
    Then I should see "Your Projects"
    And I should see "Already Studio Projects"

  Scenario: User clicks studio add button
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#show-add-studio-project-button"
    And I wait 500 milliseconds
    Then the element "#1" should be visible
    When I click "#1"
    And I click "#studio-settings__submit-button"
    And  I wait for the page to be loaded
    Then I am on "/app/studio/1"
    And I should see "project 1"
    And the "#projects-count" element should contain "2"


  Scenario: User clicks studio add button, and selects his own project for the studio
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#show-add-studio-project-button"
    And I wait 500 milliseconds
    Then the element "#2" should be visible
    When I click "#2"
    And I click "#studio-settings__submit-button"
    And  I wait for the page to be loaded
    Then  I should not see "project 2"

  Scenario: User clicks studio add button, and removes his own project from the studio
    Given I log in as "StudioAdmin"
    And I am on "/app/studio/1"
    And I wait for the page to be loaded
    When I click "#1"
    And I click "#ajaxRequestDeleteProject"
    Then  I wait for AJAX to finish
    And I wait for the page to be loaded
    And  I should not see "project 2"
    Then the "#projects-count" element should contain "0"


