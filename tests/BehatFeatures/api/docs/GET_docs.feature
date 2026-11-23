@api @docs
Feature: GET /api/docs and /api/docs/pages/{path} serve the synced user docs

  Background:
    Given the docs are synced from the test docs repo

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
    And the JSON response field "locale" should be '"de"'
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

  Scenario: Docs images are served by share itself
    When I request "GET" "/app/docs/media/tutorials/jump.png"
    Then the response status code should be "200"
    And the response Header should contain the key "Content-Type" with the value 'image/png'

  Scenario: Docs SVGs are sandboxed
    When I request "GET" "/app/docs/media/tutorials/star.svg"
    Then the response status code should be "200"
    And the response Header should contain the key "Content-Type" with the value 'image/svg+xml'

  Scenario: Other repo files are not served
    When I request "GET" "/app/docs/media/tutorials/not-served.html"
    Then the response status code should be "404"
