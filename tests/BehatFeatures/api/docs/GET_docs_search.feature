@api @docs
Feature: GET /api/docs/search finds guides and bricks by their words and by their meaning

  # The test environment's embedder (FakeDocsEmbedder) gives "jump", "springen" and "y" one meaning, so the
  # hybrid search finds what keywords alone cannot.

  Background:
    Given the docs are synced from the test docs folder
    And the docs search index is up-to-date

  Scenario: A guide by its title in English
    When I request "GET" "/api/docs/search?q=first%20project&locale=en"
    Then the response status code should be "200"
    And the JSON response field "guides.0.title" should be '"Your first project"'
    And the JSON response field "guides.0.path" should be '"getting-started\/first-project"'
    And the JSON response field "guides.0.type" should be '"guide"'
    And the JSON response field "semantic" should be 'true'
    And the response Header should contain the key "Cache-Control" with the value 'max-age=300, public'

  Scenario: A guide by its title in German, shown in German
    When I request "GET" "/api/docs/search?q=Katze%20springen&locale=de"
    Then the response status code should be "200"
    And the JSON response field "guides.0.title" should be '"Lass deine Katze springen"'
    And the JSON response field "guides.0.path" should be '"tutorials\/jump"'
    And the JSON response field "guides.0.locale" should be '"de_DE"'

  # The y bricks (Change Y by, Set y to) share no word with the question; keywords alone find none of them.
  Scenario: A paraphrased question finds the matching brick, which has none of its words
    When I request "GET" "/api/docs/search?q=make%20my%20cat%20jump&locale=en"
    Then the response status code should be "200"
    And the client response should contain "reference\/bricks\/ChangeYByNBrick"
    And the client response should contain "reference\/bricks\/SetYBrick"
    And the JSON response field "guides.0.path" should be '"tutorials\/jump"'

  Scenario: Without a locale the language of the request is used, and the answer is not cached by a CDN
    Given I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de-AT"
    When I request "GET" "/api/docs/search?q=Katze"
    Then the response status code should be "200"
    And the JSON response field "locale" should be '"de_AT"'
    And the JSON response field "guides.0.locale" should be '"de_DE"'
    And the response Header should contain the key "Cache-Control" with the value 'max-age=300, private'

  Scenario: Nothing found
    When I request "GET" "/api/docs/search?q=zzyzx&locale=en"
    Then the response status code should be "200"
    And the JSON response field "guides" should be '[]'
    And the JSON response field "bricks" should be '[]'

  Scenario Outline: A missing, empty or too long query is refused
    When I request "GET" "<url>"
    Then the response status code should be "400"

    Examples:
      | url |
      | /api/docs/search |
      | /api/docs/search?q= |
      | /api/docs/search?q=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa |
