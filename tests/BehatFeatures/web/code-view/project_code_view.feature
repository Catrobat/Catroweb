@web @project_page
Feature: As a visitor I want to see the code view on a project page
  New Code view is managed and developed by CatBlocks.
  The old view is used on errors or when not yet supported

  Scenario: For performance reasons the code view is on its own page
    Given there are projects:
      | id | name      |
      | 1  | program 1 |
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click "#projectCodeViewButton-small"
    And I wait for the page to be loaded
    Then I should be on "/app/project/1/code_view"
    And the "#top-app-bar__title" element should contain "Code view"

  Scenario: Code view provide means to return to the project page
    Given there are projects:
      | id | name      |
      | 1  | program 1 |
    And I am on "/app"
    When I go to "/app/project/1/code_view"
    And I click "#top-app-bar__back__btn-back"
    Then I should be on "/app/project/1"

  Scenario: Code view page title is no link
    Given there are projects:
      | id | name      |
      | 1  | program 1 |
    And I am on "/app/project/1/code_view"
    And I click "#top-app-bar__title"
    Then I should be on "/app/project/1/code_view"

  Scenario: The OLD code view (on old projects) should use an accordion principle @deprecated
  It must be displayed instead of the new code view
    Given I have a project zip "CodeStatistics/code_statistics_compound_blocks.catrobat"
    And I upload this generated project with id "1", API version 1
    And I am on "/app/project/1/code_view"
    And the element "#codeview-wrapper" should be visible

  @disabled
  Scenario: The code view should use an accordion principle. It must be displayed instead of the old code view
    # Disabled due to problems in the chrome headless/mink/blockly interactions
    Given I have a project zip "CodeStatistics/new_code_view.catrobat"
    And I upload this generated project with id "1", API version 1
    And I am on "/app/project/1/code_view"
    And I wait for the page to be loaded
    Then I should see "NewCodeView"
    And the element "#codeview-wrapper" should not be visible
    And the element ".catblocks-scene-header" should be visible
