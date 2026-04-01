@web @project_page
Feature: As a visitor I want to see inline code view on the project page

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |

  Scenario: Project page shows the code view toggle button
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#code-view-inline" should exist
    And the element "#code-view-toggle" should be visible

  Scenario: Code view panel is hidden by default
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#code-view-panel" should not be visible

  Scenario: Clicking toggle shows the code view panel
    Given project "1" has extracted code xml:
      """
      <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <program>
        <header>
          <programName>TestProject</programName>
          <catrobatLanguageVersion>0.99</catrobatLanguageVersion>
        </header>
        <objectList>
          <object name="Background">
            <lookList/>
            <soundList/>
            <scriptList>
              <script type="StartScript">
                <brickList>
                  <brick type="WaitBrick">
                    <formulaList>
                      <formula category="TIME_TO_WAIT_IN_SECONDS">
                        <type>NUMBER</type>
                        <value>1</value>
                      </formula>
                    </formulaList>
                  </brick>
                </brickList>
              </script>
            </scriptList>
          </object>
        </objectList>
      </program>
      """
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "Background"

  Scenario: Code view shows sprite names and scripts
    Given project "1" has extracted code xml:
      """
      <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <program>
        <header>
          <programName>TestProject</programName>
          <catrobatLanguageVersion>0.99</catrobatLanguageVersion>
        </header>
        <objectList>
          <object name="Background">
            <lookList/>
            <soundList/>
            <scriptList/>
          </object>
          <object name="Cat">
            <lookList/>
            <soundList/>
            <scriptList>
              <script type="StartScript">
                <brickList>
                  <brick type="ShowBrick"/>
                  <brick type="HideBrick"/>
                </brickList>
              </script>
            </scriptList>
          </object>
        </objectList>
      </program>
      """
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "Background"
    And I should see "Cat"

  Scenario: Clicking toggle again hides the code view panel
    Given project "1" has extracted code xml:
      """
      <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <program>
        <header>
          <programName>TestProject</programName>
          <catrobatLanguageVersion>0.99</catrobatLanguageVersion>
        </header>
        <objectList>
          <object name="Background">
            <lookList/>
            <soundList/>
            <scriptList/>
          </object>
        </objectList>
      </program>
      """
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "Background"
    When I click "#code-view-toggle"
    Then the element "#code-view-panel" should not be visible

  Scenario: Code view shows error state when no extracted files exist
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "Try again"
    And the element ".cv-retry-btn" should be visible
