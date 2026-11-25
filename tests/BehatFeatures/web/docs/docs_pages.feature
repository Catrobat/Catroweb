@web @docs
Feature: User docs pages, synced from user-docs/

  Background:
    Given the docs are synced from the test docs folder

  Scenario Outline: Every page of the repo renders in English
    When I go to "<url>"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "<title>"
    And I should see "Improve this page"

    Examples:
      | url                                 | title                  |
      | /app/docs                           | Catrobat documentation |
      | /app/docs/getting-started           | Getting started        |
      | /app/docs/getting-started/first-project | Your first project |
      | /app/docs/tutorials/jump            | Make your cat jump     |

  Scenario: The embeds render
    Given I use a 1280 x 900 viewport
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then I should see a ".docs-embed--brick[data-brick-type='SetXBrick']" element
    And I should see a ".docs-embed--formula[data-formula-name='SIN']" element
    And I should see a ".docs-video[data-youtube-id='dQw4w9WgXcQ']" element
    And I should see a ".docs-embed--project-missing" element
    And the element ".docs-article > .docs-toc" should contain "Move the cat"
    And the element ".docs-pager" should contain "Make your cat jump"

  Scenario: A curated project has a local preview and starts with the player unloaded
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id                                   | name         | owned by | language version |
      | 11111111-1111-4111-8111-111111111111 | Glide sample | Catrobat | 0.999            |
    And the docs are synced from the test docs folder
    When I go to "/app/docs/tutorials/jump"
    And I wait for the page to be loaded
    Then I should see a ".docs-project-preview img[src^='/docs-media/']" element
    And the element ".docs-project-preview__play" should contain "Play an example"
    And I should not see a ".docs-content iframe" element

  Scenario: A video loads from YouTube only after a click
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then I should not see an "iframe.docs-video__frame" element
    When I click ".docs-video"
    Then I should see an "iframe.docs-video__frame[src^='https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ']" element

  Scenario: The app sidebar lists the docs sections, flavour sections first, only on docs pages
    Given I use a 1280 x 900 viewport
    When I go to "/embroidery/docs"
    And I wait for the page to be loaded
    Then I should see a "#sidebar #btn-docs[aria-current='page']" element
    And the element "#sidebar .sidebar-docs-sections" should contain "Getting started"
    And the element "#sidebar .sidebar-docs-sections" should contain "Bricks and formulas"
    And the element "#sidebar .sidebar-docs-sections > li:first-child" should contain "Make your cat jump"
    And I should not see a "#sidebar .sidebar-docs-sections a[href$='/docs']" element
    When I go to "/app/"
    And I wait for the page to be loaded
    Then I should see a "#sidebar #btn-docs" element
    And I should not see a "#sidebar .sidebar-docs-sections" element

  Scenario: On a page the sidebar marks its section, the breadcrumb leads back and the section's pages are beside it
    Given I use a 1280 x 900 viewport
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then the element "#sidebar .sidebar-docs-section--current[aria-current='true']" should contain "Getting started"
    And the element ".docs-breadcrumb li:nth-child(1) a" should contain "Documentation"
    And the element ".docs-breadcrumb li:nth-child(2) a" should contain "Getting started"
    And the element ".docs-breadcrumb [aria-current='page']" should contain "Your first project"
    And I should see a "details.docs-section[open]" element
    And I should see a ".docs-section__summary[tabindex='-1'][aria-hidden='true']" element
    And the element ".docs-section__link--current[aria-current='page']" should contain "Your first project"
    And I should not see a ".docs-section__all" element

  Scenario: A phone lesson shows its goal and puts section navigation behind one control
    Given I use a 390 x 844 viewport
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then I should see a "details.docs-section:not([open])" element
    And I should not see a ".docs-section__summary[tabindex]" element
    And the element ".docs-lesson-goal" should contain "Make an object move."
    When I activate the button ".docs-section__summary"
    Then I should see a "details.docs-section[open]" element
    And the element ".docs-section .docs-breadcrumb" should contain "Getting started"
    And the element ".docs-section .docs-toc" should contain "Move the cat"

  Scenario: A section's page lists its pages, the docs home lists the sections
    Given I use a 1280 x 900 viewport
    When I go to "/app/docs/getting-started"
    And I wait for the page to be loaded
    Then the element ".docs-pages" should contain "Your first project"
    And I should not see a ".docs-section" element
    And the element "#sidebar .sidebar-docs-section--current[aria-current='page']" should contain "Getting started"
    When I go to "/app/docs"
    And I wait for the page to be loaded
    Then the element ".docs-pages" should contain "Getting started"
    And the element ".docs-pages" should contain "Bricks and formulas"
    And I should not see a ".docs-breadcrumb" element

  Scenario: The docs home offers four clear choices on a phone
    Given I use a 390 x 844 viewport
    When I go to "/app/docs"
    And I wait for the page to be loaded
    Then the element ".docs-home__featured" should contain "Make your first game"
    And I should see a ".docs-home__featured a[href$='/docs/game-tutorials/starter-tutorials'] img" element
    And I should see a ".docs-home__cards li:nth-child(2) a[href$='/docs/reference']" element
    And I should see a ".docs-home__cards li:nth-child(3) a[href$='/docs/embroidery-tutorials']" element
    And I should see a ".docs-home__cards li:nth-child(4) a[href$='/docs/plot-tutorials']" element

  Scenario: Home cards keep the other sections and use the selected docs language
    When I go to "/app/docs?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-home__featured" should contain "Mach dein erstes Spiel"
    And I should see a ".docs-home__other a[href$='/docs/tutorials/jump?locale=de']" element

  Scenario: The embroidery docs feature the embroidery card
    When I go to "/embroidery/docs"
    And I wait for the page to be loaded
    Then the element ".docs-home__featured" should contain "Embroidery"
    And I should see a ".docs-home__featured a[href$='/docs/embroidery-tutorials']" element

  Scenario: An untranslated page falls back to English and says so
    When I go to "/app/docs/getting-started?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Getting started"
    And I should see a ".docs-note" element

  Scenario: A translation is shown in its language
    When I go to "/app/docs?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Catrobat-Dokumentation"

  Scenario: A phone reader can follow the next lesson card and go back
    Given I use a 390 x 844 viewport
    When I go to "/app/docs/getting-started/first-project?locale=en"
    And I wait for the page to be loaded
    Then the element ".docs-pager--lesson .docs-pager__link--next strong" should contain "Make your cat jump"
    And I should see a ".docs-pager > :first-child[rel='prev']" element
    When I click ".docs-pager__link--next"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Make your cat jump"
    And I should see a ".docs-pager__link--previous[href$='/docs/getting-started/first-project?locale=en']" element

  Scenario: The pager translates its label for a short docs locale
    When I go to "/app/docs/getting-started/first-project?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-pager__link--next .docs-pager__label" should contain "Weiter"

  Scenario: An explicit docs language also applies to the guide and brick interface
    Given I use a 1280 x 900 viewport
    And I am on the page "/app/docs/tutorials/jump?locale=de" with header "Accept-Language" equal to "en-US"
    And I wait for the page to be loaded
    Then I should see an "html[lang|='de']" element
    And the element ".docs-breadcrumb li:first-child" should contain "Dokumentation"
    And the element "#sidebar .sidebar-docs-sections" should contain "Bausteine und Formeln"
    And the element ".docs-article > .docs-toc .docs-toc__title" should contain "Auf dieser Seite"
    And I should see "Kommentare und Tipps"
    And I should not see "Bricks and formulas"
    And I should not see "On this page"
    And I should not see "Comments and tips"
    When I am on the page "/app/docs/reference/bricks/SetXBrick?locale=de" with header "Accept-Language" equal to "en-US"
    And I wait for the page to be loaded
    Then I should see an "html[lang|='de']" element
    And the element ".docs-breadcrumb" should contain "Bausteine und Formeln"
    And I should see "Kommentare und Tipps"
    And I should not see "Bricks and formulas"
    And I should not see "Comments and tips"

  Scenario: Article and navigation links keep an explicitly chosen language
    Given I use a 1280 x 900 viewport
    When I go to "/app/docs/getting-started/first-project?locale=de_DE"
    And I wait for the page to be loaded
    Then I should see a ".docs-content a[href='../tutorials/jump?locale=de_DE#jump-higher']" element
    And I should see a ".docs-pager a[href$='?locale=de_DE']" element
    And I should see a "#sidebar .sidebar-docs-sections a[href$='?locale=de_DE']" element
    When I click ".docs-content a[href='../../docs?locale=de_DE']"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Catrobat-Dokumentation"

  Scenario: A missing page is not found
    When I go to "/app/docs/does-not-exist"
    Then I should not see a ".docs-article" element
