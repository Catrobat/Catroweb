@api @docs
Feature: GET /api/docs and /api/docs/pages/{path} serve the synced user docs

  Background:
    Given the docs are synced from the test docs folder

  Scenario: The navigation tree
    When I request "GET" "/api/docs?locale=de&flavor=embroidery"
    Then the response status code should be "200"
    And the JSON response field "locale" should be '"de"'
    And the JSON response field "items.0.title" should be '"Lass deine Katze springen"'
    And the JSON response field "items.1.title" should be '"Catrobat-Dokumentation"'
    And the JSON response field "items.2.children.0.title" should be '"Your first project"'

  Scenario: A page in English
    When I request "GET" "/api/docs/pages/getting-started/first-project"
    Then the response status code should be "200"
    And the JSON response field "title" should be '"Your first project"'
    And the JSON response field "locale" should be '"en"'
    And the JSON response field "fallback" should be 'false'
    And the JSON response field "translation_state" should be '"source"'
    And the JSON response field "toc.0.id" should be '"move-the-cat"'
    And the client response should contain "docs-embed--brick"

  Scenario: A translation that is stale
    When I request "GET" "/api/docs/pages/tutorials/jump?locale=de_DE"
    Then the response status code should be "200"
    And the JSON response field "locale" should be '"de_DE"'
    And the JSON response field "fallback" should be 'false'
    And the JSON response field "translation_state" should be '"stale"'
    And the JSON response field "reviewed" should be 'true'

  Scenario: A page without translation falls back to English and says so
    When I request "GET" "/api/docs/pages/getting-started?locale=de"
    Then the response status code should be "200"
    And the JSON response field "locale" should be '"en"'
    And the JSON response field "fallback" should be 'true'

  Scenario: Brick explanations are not guide pages
    When I request "GET" "/api/docs/pages/SetXBrick"
    Then the response status code should be "404"

  Scenario: An unknown page
    When I request "GET" "/api/docs/pages/does-not-exist"
    Then the response status code should be "404"

  # The files themselves are static (public/docs-media), served by the web server, not by Symfony.
  Scenario: Docs images link to share's static copy
    When I request "GET" "/api/docs/pages/getting-started/first-project"
    Then the response status code should be "200"
    And the client response should contain "\/docs-media\/tutorials\/jump.png"
    And the client response should not contain "_media"

  Scenario Outline: The language of the request finds the share locale folder (de_DE/)
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "<language>"
    When I request "GET" "/api/docs/pages/index"
    Then the response status code should be "200"
    And the JSON response field "locale" should be '"de_DE"'
    And the JSON response field "fallback" should be 'false'

    Examples:
      | language |
      | de       |
      | de-AT    |

  Scenario Outline: The docs page is shown in the visitor's language
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "<language>"
    When I request "GET" "/app/docs"
    Then the response status code should be "200"
    And the client response should contain "Catrobat-Dokumentation"

    Examples:
      | language |
      | de       |
      | de-AT    |

  Scenario: A page as Markdown, for AI assistants
    When I request "GET" "/app/docs/getting-started/first-project.md"
    Then the response status code should be "200"
    And the response Header should contain the key "Content-Type" with the value 'text/markdown; charset=utf-8'
    And the client response should contain "# Your first project"
    And the client response should contain "/app/docs/tutorials/jump#jump-higher"

  Scenario: llms.txt lists the docs as Markdown
    When I request "GET" "/llms.txt"
    Then the response status code should be "200"
    And the client response should contain "# Catrobat documentation"
    And the client response should contain "/app/docs/getting-started/first-project.md"

  Scenario: A missing Markdown page is a plain 404, not a redirect to the homepage
    When I request "GET" "/app/docs/does-not-exist.md"
    Then the response status code should be "404"
