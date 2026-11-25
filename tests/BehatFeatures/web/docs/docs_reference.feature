@web @docs
Feature: Brick and formula reference pages, generated from Catroid's catalog

  Background:
    Given the docs are synced from the test docs folder

  Scenario: The reference lists the bricks by category, in the code view's colours (navigation open on a wide screen)
    Given I use a 1280 x 900 viewport
    When I go to "/app/docs/reference"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Bricks and formulas"
    And I should see a "#category_motion .docs-reference__heading.cv-block--motion" element
    And I should see a "#category_motion a[href$='/docs/reference/bricks/SetXBrick?category=category_motion'] .cv-block--motion" element
    And I should see a "#formula a[href$='/docs/reference/formula/SIN']" element
    And the element "#sidebar .sidebar-docs-section--current[aria-current='page']" should contain "Bricks and formulas"
    And the element ".docs-breadcrumb [aria-current='page']" should contain "Bricks and formulas"

  Scenario: A brick page sits in its category: breadcrumb and the category's other bricks
    Given I use a 1280 x 900 viewport
    When I go to "/app/docs/reference/bricks/SetXBrick"
    And I wait for the page to be loaded
    Then the element "#sidebar .sidebar-docs-section--current[aria-current='true']" should contain "Bricks and formulas"
    And the element ".docs-breadcrumb li:nth-child(2) a" should contain "Bricks and formulas"
    And I should see a ".docs-breadcrumb li:nth-child(3) a[href$='/docs/reference#category_motion']" element
    And the element ".docs-section__link--current[aria-current='page']" should contain "Set x to 100"

  Scenario: A brick page shows the brick, its explanation and its example projects
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are projects:
      | id | name    | owned by | downloads | language version |
      | 1  | Jumping | Catrobat | 30        | 0.999            |
    And there are project code statistics:
      | project_id | brick_counts |
      | 1          | SetXBrick:2  |
    When I go to "/app/docs/reference/bricks/SetXBrick"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Set x to 100"
    And the element ".docs-reference__shown .cv-block--motion" should contain "Set x to"
    And the element ".docs-content" should contain "puts the object at a position"
    And the element ".docs-reference__meta" should contain "Motion"
    And I wait for AJAX to finish
    And the element ".brick-examples" should contain "Jumping"
    And I should see a ".docs-improve a[href$='/edit/main/user-docs/reference/bricks/SetXBrick.md']" element
    And I should see a "#comments.docs-comments" element

  Scenario: A brick page in another language
    When I go to "/app/docs/reference/bricks/SetXBrick?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Setze x auf 100"
    And the element ".docs-content" should contain "setzt das Objekt an eine Stelle"

  Scenario: A brick the app shows only after a setting is switched on says which setting
    When I go to "/app/docs/reference/bricks/LegoNxtMotorTurnAngleBrick?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-note[role='note']" should contain "Damit du diesen Baustein in der App siehst, schalte in den Einstellungen „Erweiterung Lego Mindstorms NXT“ ein."

  Scenario: A brick without explanation invites to write one
    When I go to "/app/docs/reference/bricks/StartScript"
    And I wait for the page to be loaded
    Then I should see a ".docs-note--quiet" element
    And I should see a ".docs-improve a[href$='/new/main/user-docs/reference/bricks?filename=StartScript.md']" element

  Scenario: A formula element page
    When I go to "/app/docs/reference/formula/SIN"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "sine"
    And the element ".docs-reference__meta" should contain "Functions"

  Scenario: An unknown brick is not found
    When I go to "/app/docs/reference/bricks/NoSuchBrick"
    Then I should not see a ".docs-reference__shown" element

  Scenario: Guides show embedded bricks as the reference does and link to their pages
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then I should see a "a.docs-embed--brick[href='../reference/bricks/SetXBrick'] .cv-block--motion" element
    And the element ".docs-embed--brick" should contain "Set x to"
    And I should see a "a.docs-embed--formula[href='../reference/formula/SIN']" element

  Scenario: Common jump links come first and a brick keeps the group it was opened from
    Given I use a 390 x 844 viewport
    When I go to "/app/docs/reference"
    And I wait for the page to be loaded
    Then the element ".docs-reference__jump-list li:first-child" should contain "Start"
    And I should see a ".docs-reference__jump-list a[href='#category_motion']" element
    And I should see a ".docs-reference__jump details:not([open])" element
    When I click "#category_device a[href$='/docs/reference/bricks/SetXBrick?category=category_device']"
    And I wait for the page to be loaded
    Then the element ".docs-reference__category-link--primary" should contain "Device"
    And I should see a ".docs-reference__category-link--primary[href$='/docs/reference#category_device']" element
