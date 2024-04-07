@web @project_page
Feature: Stealing projects via the steal button

  Background:
    Given there are users:
      | id | name      |
      | 1  | Catrobat  |
      | 2  | OtherUser |
    And there are projects:
      | id | name      | owned by  |
      | 1  | project 1 | OtherUser |
      | 2  | project 2 | OtherUser |

  Scenario: Stealing a project by clicking the steal button
    Given I log in as "Catrobat"
    And I go to "/app/project/2"
    And I wait for the page to be loaded
    Then the element "#stealButton-small" should be visible
    When I click "#stealButton-small"
    And I wait for AJAX to finish
    And I wait for the page to be loaded
    Then I should see "You successfully stole the project!"