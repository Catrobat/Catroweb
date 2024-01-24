@web
Feature: On some page the top app bar should provide the user with additional functionalities.

  Scenario: The options button should only be visible on specific pages
    Given I am on "/app"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-options" should not exist

  Scenario: The options button should be visible on project pages
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-options" should be visible

# # TODO: Report project feature currently disabled
#  Scenario: The options should contain a report button on project pages
#    Given there are users:
#      | id | name     |
#      | 1  | Catrobat |
#    And there are projects:
#      | id | name      | owned by |
#      | 1  | project 1 | Catrobat |
#    And I am on "/app/project/1"
#    And I wait for the page to be loaded
#    Then the element "#top-app-bar__default" should be visible
#    And the element "#top-app-bar__btn-options" should be visible
#    Then the element "#top-app-bar__btn-report-project" should not be visible
#    When I click "#top-app-bar__btn-options"
#    Then the element "#top-app-bar__btn-report-project" should be visible

  Scenario: The options should contain a share button on project pages
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-options" should be visible
    Then the element "#top-app-bar__btn-share" should not be visible
    When I click "#top-app-bar__btn-options"
    Then the element "#top-app-bar__btn-share" should be visible

  Scenario: The options should contain a share button on user pages
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name      | owned by |
      | 1  | project 1 | Catrobat |
    And I am on "/app/user/1"
    And I wait for the page to be loaded
    Then the element "#top-app-bar__default" should be visible
    And the element "#top-app-bar__btn-options" should be visible
    Then the element "#top-app-bar__btn-share" should not be visible
    When I click "#top-app-bar__btn-options"
    Then the element "#top-app-bar__btn-share" should be visible
