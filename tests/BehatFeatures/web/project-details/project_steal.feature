@web @project_page
Feature: As a visitor I want to be able to steal projects

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Catrobad |
    And there are projects:
      | id | name      | private | owned by |
      | 1  | project 1 | 0       | Catrobat |

  Scenario: I want to steal a project via the button
    Given I am logged in as normal user
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#projectStealButton-small" should be visible
    And I click "#projectStealButton-small"
    And I wait 150 milliseconds
    And the element "#share-snackbar" should be visible
    And I should not see "Error while stealing the project"
    And I should see "Stealing Project..."