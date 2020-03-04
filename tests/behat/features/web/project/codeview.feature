@web @project_page
Feature: As a visitor I want to see the code view on a project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |

    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |

  Scenario: The Code view and code statistics should be collapsed per default
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And I should see "SHOW CODE STATISTICS"
    And I should see "SHOW CODE"
    But I should not see "HIDE CODE STATISTICS"
    And I should not see "HIDE CODE"

  Scenario: The User should be able to toggle the code view
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And the ".show-hide-code-text" element should contain "SHOW CODE"
    But the ".show-hide-code-text" element should not contain "HIDE CODE"
    When I click ".show-hide-code-arrow"
    And I wait for AJAX to finish
    Then the ".show-hide-code-text" element should not contain "SHOW CODE"
    But the ".show-hide-code-text" element should contain "HIDE CODE"

  Scenario: The User should be able to toggle the code statistics
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "project 1"
    And the ".show-hide-code-statistic-text" element should contain "SHOW CODE STATISTICS"
    But the ".show-hide-code-statistic-text" element should not contain "HIDE CODE STATISTICS"
    When I click ".show-hide-code-statistic-arrow"
    And I wait for AJAX to finish
    Then the ".show-hide-code-statistic-text" element should not contain "SHOW CODE STATISTICS"
    But the ".show-hide-code-statistic-text" element should contain "HIDE CODE STATISTICS"
