@web @project_page
Feature: As a visitor I want to see the code view on a project page
         New Code view is managed and developed by CatBlocks.
         The old view is used on errors or when not yet supported

  Scenario: The OLD code view (on old projects) should use an accordion principle @deprecated
            It must be displayed instead of the new code view
    Given I have a project zip "CodeStatistics/code_statistics_compound_blocks.catrobat"
    And I upload this generated program with id "1", API version 1
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#codeView" should be visible
    And the element "#codeview-wrapper" should not be visible
    When I click "#codeView"
    Then the element "#codeView" should be visible
    And the element "#codeview-wrapper" should be visible
    When I click "#codeView"
    Then the element "#codeView" should be visible
    And the element "#codeview-wrapper" should not be visible

  @disabled
  Scenario: The code view should use an accordion principle. It must be displayed instead of the old code view
    # Disabled due to problems in the chrome headless/mink/blockly interactions
    Given I have a project zip "CodeStatistics/new_code_view.catrobat"
    And I upload this generated program with id "1", API version 1
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then I should see "NewCodeView"
    Then the element "#codeView" should be visible
    And the element "#codeview-wrapper" should not be visible
    And no ".catblocks-scene-header" element should be visible
    When I click "#codeView"
    Then the element "#codeView" should be visible
    And the element "#codeview-wrapper" should not be visible
    And the element ".catblocks-scene-header" should be visible
    When I click "#codeView"
    Then the element "#codeView" should be visible
    And the element "#codeview-wrapper" should not be visible
    And no ".catblocks-scene-header" element should be visible

  Scenario: The code view should use an accordion principle coupled to the code statistics
    Given I have a project zip "CodeStatistics/code_statistics_compound_blocks.catrobat"
    And I upload this generated program with id "1", API version 1
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#codeView" should be visible
    Then the element "#statistics" should be visible
    And the element "#statisticsGeneral" should not be visible
    And the element "#codeview-wrapper" should not be visible
    When I click "#codeView"
    Then the element "#statistics" should be visible
    Then the element "#codeView" should be visible
    And the element "#statisticsGeneral" should not be visible
    And the element "#codeview-wrapper" should be visible
    When I click "#statistics"
    Then the element "#statistics" should be visible
    Then the element "#codeView" should be visible
    And the element "#statisticsGeneral" should be visible
    And the element "#codeview-wrapper" should not be visible
