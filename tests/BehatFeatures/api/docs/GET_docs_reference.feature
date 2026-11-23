@api @docs
Feature: The brick and formula reference, generated from Catroid's catalog (tests/TestData/CodeExport/catalog.json)

  Background:
    Given the docs are synced from the test docs folder

  Scenario Outline: Every brick has its page and API answer, all of its strings filled in
    Then every brick of the docs catalog has a page in "<locale>"

    Examples:
      | locale |
      | en     |
      | de     |

  Scenario: The catalog in a language
    When I request "GET" "/api/docs/reference/catalog?locale=de"
    Then the response status code should be "200"
    And the JSON response field "locale" should be '"de"'
    And the JSON response field "formula.functions.0.name" should be '"SIN"'
    And the JSON response field "formula.functions.0.label" should be '"Sinus"'
    And the client response should contain "Setze x auf"
    And the response Header should contain the key "Cache-Control" with the value 'max-age=300, public'

  Scenario: Without locale the catalog follows Accept-Language and no CDN may share it
    When I request "GET" "/api/docs/reference/catalog"
    Then the response status code should be "200"
    And the response Header should contain the key "Cache-Control" with the value 'max-age=300, private'

  Scenario: A brick with its explanation, in English where it has no translation
    When I request "GET" "/api/docs/reference/bricks/SetXBrick?locale=fr"
    Then the response status code should be "200"
    And the JSON response field "type" should be '"SetXBrick"'
    And the JSON response field "categories.0.key" should be '"category_motion"'
    And the JSON response field "categories.0.color" should be '"motion"'
    And the JSON response field "html_locale" should be '"en"'
    And the JSON response field "fallback" should be 'true'
    And the client response should contain "puts the object at a position"
    And the client response should contain "edit\/main\/user-docs\/reference\/bricks\/SetXBrick.md"

  Scenario: A brick with a translated explanation
    When I request "GET" "/api/docs/reference/bricks/SetXBrick?locale=de"
    Then the response status code should be "200"
    And the JSON response field "title" should be '"Setze x auf 100"'
    And the JSON response field "html_locale" should be '"de_DE"'
    And the JSON response field "fallback" should be 'false'

  Scenario: A brick without explanation links to a new file
    When I request "GET" "/api/docs/reference/bricks/StartScript"
    Then the response status code should be "200"
    And the client response should not contain "html_locale"
    And the client response should contain "new\/main\/user-docs\/reference\/bricks?filename=StartScript.md"

  Scenario: An unknown brick is not found
    When I request "GET" "/api/docs/reference/bricks/NoSuchBrick"
    Then the response status code should be "404"

  Scenario: A brick type that is not a class name is refused
    When I request "GET" "/api/docs/reference/bricks/1Brick"
    Then the response status code should be "404"

  Scenario Outline: Old wiki URLs name a brick by its class and are sent to its page
    Given I do not follow redirects
    When I request "GET" "/app/docs/reference/bricks/by-class/<class>"
    Then the response status code should be "301"
    And the response Location header should be "/app/docs/reference/bricks/<type>"

    Examples:
      | class            | type        |
      | SetXBrick        | SetXBrick   |
      | WhenStartedBrick | StartScript |
      | WhenBrick        | WhenScript  |

  Scenario: An unknown class goes to the reference
    Given I do not follow redirects
    When I request "GET" "/app/docs/reference/bricks/by-class/NoSuchBrick"
    Then the response status code should be "302"
    And the response Location header should be "/app/docs/reference"

  Scenario Outline: The brick section imported from catrobat.org is folded into the reference
    Given I do not follow redirects
    When I request "GET" "<url>"
    Then the response status code should be "301"
    And the response Location header should be "<location>"

    Examples:
      | url                               | location                                             |
      | /app/docs/bricks                  | /app/docs/reference                                  |
      | /app/docs/bricks/looks            | /app/docs/reference#category_looks                   |
      | /app/docs/bricks/yourbricks       | /app/docs/reference#category_user_bricks             |
      | /app/docs/bricks/motion?locale=de | /app/docs/reference?locale=de#category_motion        |
      | /app/docs/de_DE/bricks/embroidery | /app/docs/reference?locale=de_DE#category_embroidery |

  Scenario: The app's language is kept, other query values are not
    Given I do not follow redirects
    When I request "GET" "/app/docs/reference/bricks/by-class/SetXBrick?locale=de&theme=luna"
    Then the response status code should be "301"
    And the response Location header should be "/app/docs/reference/bricks/SetXBrick?locale=de"

  Scenario: An unknown class keeps the language too
    Given I do not follow redirects
    When I request "GET" "/app/docs/reference/bricks/by-class/NoSuchBrick?locale=de"
    Then the response status code should be "302"
    And the response Location header should be "/app/docs/reference?locale=de"
