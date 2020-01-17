@homepage
Feature: As a visitor I want to see a project page

  Background:
    Given there are users:
      | name     | password | token      | email               | id |
      | catrobat | 123456   | cccccccccc | dev1@pocketcode.org | 1  |

    And there are programs:
      | id | name      | description             | owned by |
      | 1  | project 1 | my superman description | catrobat |


  Scenario: The Code view and code statistics should be collapsed per default
    Given I am on "/app/project/1"
    Then I should see "project 1"
    And I should see "SHOW CODE STATISTICS"
    And I should see "SHOW CODE"
    And I should not see "HIDE CODE STATISTICS"
    And I should not see "HIDE CODE"

  Scenario: The User should be able to toggle the code view
    Given I am on "/app/project/1"
    Then I should see "project 1"
    And the ".show-hide-code-text" element should contain "SHOW CODE"
    And the ".show-hide-code-text" element should not contain "HIDE CODE"
    When I click ".show-hide-code-arrow"
    And the ".show-hide-code-text" element should not contain "SHOW CODE"
    And the ".show-hide-code-text" element should contain "HIDE CODE"

  Scenario: The User should be able to toggle the code statistics
    Given I am on "/app/project/1"
    Then I should see "project 1"
    And the ".show-hide-code-statistic-text" element should contain "SHOW CODE STATISTICS"
    And the ".show-hide-code-statistic-text" element should not contain "HIDE CODE STATISTICS"
    When I click ".show-hide-code-statistic-arrow"
    And the ".show-hide-code-statistic-text" element should not contain "SHOW CODE STATISTICS"
    And the ".show-hide-code-statistic-text" element should contain "HIDE CODE STATISTICS"
