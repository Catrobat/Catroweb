@web @docs
Feature: User docs pages, synced from the docs repo

  Background:
    Given the docs are synced from the test docs repo

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
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then I should see a ".docs-embed--brick[data-brick-type='SetXBrick']" element
    And I should see a ".docs-embed--formula[data-formula-name='SIN']" element
    And I should see a ".docs-video[data-youtube-id='dQw4w9WgXcQ']" element
    And I should see a ".docs-embed--project-missing" element
    And the element ".docs-toc" should contain "Move the cat"
    And the element ".docs-pager" should contain "Make your cat jump"

  Scenario: A video loads from YouTube only after a click
    When I go to "/app/docs/getting-started/first-project"
    And I wait for the page to be loaded
    Then I should not see an "iframe.docs-video__frame" element
    When I click ".docs-video"
    Then I should see an "iframe.docs-video__frame[src^='https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ']" element

  Scenario: Navigation lists the pages, flavour pages first (open beside the page on a wide screen)
    Given I use a 1280 x 900 viewport
    When I go to "/embroidery/docs"
    And I wait for the page to be loaded
    Then the element ".docs-nav" should contain "Make your cat jump"
    And the element ".docs-nav .docs-nav__list > li:first-child" should contain "Make your cat jump"

  Scenario: An untranslated page falls back to English and says so
    When I go to "/app/docs/getting-started?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Getting started"
    And I should see a ".docs-note" element

  Scenario: A translation is shown in its language
    When I go to "/app/docs?locale=de"
    And I wait for the page to be loaded
    Then the element ".docs-article__title" should contain "Catrobat-Dokumentation"

  Scenario: A missing page is not found
    When I go to "/app/docs/does-not-exist"
    Then I should not see a ".docs-article" element
