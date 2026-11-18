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

  @code_view_translation
  Scenario: Translating and restoring the code keeps the sprite expanded
    Given project "1" has extracted code xml:
      """
      <program><object name="Cat"><look name="Costume"/><script type="StartScript">
        <brick type="NoteBrick" text="Hola, ¿quieres jugar?"/>
      </script></object>
      <object name="Dog"><script type="StartScript">
        <brick type="SpeakBrick" text="Este es el texto del perro."/>
      </script></object></program>
      """
    And I am on "/api/projects/1/code"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click ".cookie-consent-decline"
    When I activate the button "#code-view-toggle"
    And I wait for the element "#code-view-panel" to contain "Hola, ¿quieres jugar?"
    And I click ".cv-sprite:first-child [data-tab='looks']"
    And I click ".cv-sprite:nth-of-type(2) .cv-sprite__header"
    And I activate the button "[data-code-translate]"
    And I wait for the element "[data-code-translation-status]" to contain "Machine translated into British English"
    When I select "fr" from "code-view-target-language"
    Then I wait for the element "[data-code-translate]" to contain "Translate to French"
    And I wait for the element "[data-code-translation-status]" to contain "Still showing British English"
    When I activate the button "[data-code-translate]"
    Then I wait for the element "#code-view-panel" to contain "translated Este es el texto del perro."
    And the element ".cv-sprite:nth-of-type(2) .cv-sprite__body" should be visible
    And the element ".cv-sprite:first-child [data-tab-panel='looks']" should be visible
    When I activate the button "[data-code-original]"
    Then I should not see "translated Este es el texto del perro."
    And I wait for the element "#code-view-panel" to contain "Este es el texto del perro."
    And the element ".cv-sprite:nth-of-type(2) .cv-sprite__body" should be visible
    And the element ".cv-sprite:first-child [data-tab-panel='looks']" should be visible

  @code_view_translation
  Scenario: Visitors can choose the source for a project with only variable names
    Given project "1" has extracted code xml:
      """
      <program><object name="Cat"><script type="StartScript">
        <brick type="SetVariableBrick" variable="puntos"/>
      </script></object></program>
      """
    And I am on "/api/projects/1/code"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click ".cookie-consent-decline"
    When I activate the button "#code-view-toggle"
    And I wait for the element "#code-view-panel" to contain "puntos"
    And I activate the button "[data-code-translate]"
    Then I wait for the element "[data-code-translation-status]" to contain "Choose the source language"
    When I select "fr" from "code-view-source-language"
    And I activate the button "[data-code-translate]"
    Then I wait for the element "#code-view-panel" to contain "translated puntos"
    And the element ".cv-field--choice .cv-translation-name" should be visible
    And the element ".cv-field:not(.cv-field--choice) .cv-translation-name" should be visible
    When I click ".cv-field--choice .cv-translation-name"
    Then I wait for the element ".tooltip .tooltip-inner" to contain "puntos"
    When I activate the button "[data-code-original]"
    Then I should not see "translated puntos"

  @code_view_translation
  Scenario: A stale translation request reloads the code view
    Given project "1" has extracted code xml:
      """
      <program><object name="Cat"><script type="StartScript">
        <brick type="NoteBrick" text="The original note."/>
      </script></object></program>
      """
    And I am on "/api/projects/1/code"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    And I click ".cookie-consent-decline"
    When I activate the button "#code-view-toggle"
    And I wait for the element "#code-view-panel" to contain "The original note."
    Given project "1" has extracted code xml:
      """
      <program><object name="Cat"><script type="StartScript">
        <brick type="NoteBrick" text="The updated note."/>
      </script></object></program>
      """
    When I activate the button "[data-code-translate]"
    Then I wait for the element "#code-view-panel" to contain "The updated note."
    And I should not see "The original note."

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
    When I activate the button "#code-view-toggle"
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
    When I activate the button "#code-view-toggle"
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
    When I activate the button "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "Background"
    When I activate the button "#code-view-toggle"
    Then the element "#code-view-panel" should not be visible

  Scenario: Code view shows the bricks as the app does, made by the code export
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
                  <brick type="WaitBrick"/>
                </brickList>
              </script>
            </scriptList>
          </object>
        </objectList>
      </program>
      """
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I activate the button "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "When scene starts"
    And the element ".cv-field" should exist
    And I should see "Wait"
    And I should see "second"

  Scenario: Code view says so when a project's code cannot be shown
    Given project "1" has extracted code xml:
      """
      <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
      <program>
        <!-- BROKEN: the test code export fails on this -->
        <objectList/>
      </program>
      """
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I activate the button "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "code can't be shown"
    And the element ".cv-retry-btn" should not exist

  Scenario: Code view says so when the project has no files
    Given project "1" has no extracted files
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I activate the button "#code-view-toggle"
    And I wait for AJAX to finish
    Then I wait for the element "#code-view-panel" to contain "code can't be shown"
