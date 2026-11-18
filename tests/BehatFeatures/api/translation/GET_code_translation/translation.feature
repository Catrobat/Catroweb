@api @code_view_translation
Feature: Translate project text in the code view

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name    | owned by |
      | 1  | Project | Catrobat |
    And project "1" has extracted code xml:
      """
      <program><object name="Cat"><script type="StartScript">
        <brick type="NoteBrick" text="Hello there, friend!"/>
      </script></object></program>
      """

  Scenario: An uncached translation is queued and can be polled
    When I request "GET" "/api/projects/1/code/translation?target_language=de"
    Then the response status code should be "202"
    When I request "GET" "/api/projects/1/code/translation?target_language=de&poll=true"
    Then the response status code should be "200"
    And the client response should contain "translated Hello there, friend!"

  Scenario: An outdated view must be reloaded before translation
    Given I have a request header "HTTP_X_CODE_EXPORT_KEY" with value "old-export"
    When I request "GET" "/api/projects/1/code/translation?target_language=de"
    Then the response status code should be "409"

  Scenario: A project hidden from the visitor cannot be translated
    Given project "1" is not visible
    When I request "GET" "/api/projects/1/code/translation?target_language=de"
    Then the response status code should be "404"

  Scenario: Polling reports an unsupported source language
    When I request "GET" "/api/projects/1/code/translation?target_language=de&source_language=ru"
    Then the response status code should be "202"
    When I request "GET" "/api/projects/1/code/translation?target_language=de&source_language=ru&poll=true"
    Then the response status code should be "422"

  Scenario: A large project returns a resumable partial result
    Given project "1" has extracted code xml:
      """
      <program><object name="Cat"><script type="StartScript">
        <brick type="NoteBrick" text="First note"/>
        <brick type="NoteBrick" text="Second note"/>
        <brick type="NoteBrick" text="Third note"/>
        <brick type="NoteBrick" text="Fourth note"/>
        <brick type="NoteBrick" text="Fifth note"/>
        <brick type="NoteBrick" text="Sixth note"/>
        <brick type="NoteBrick" text="Seventh note"/>
        <brick type="NoteBrick" text="Eighth note"/>
        <brick type="NoteBrick" text="Ninth note"/>
        <brick type="NoteBrick" text="Tenth note"/>
        <brick type="NoteBrick" text="Eleventh note"/>
        <brick type="NoteBrick" text="Twelfth note"/>
        <brick type="NoteBrick" text="Thirteenth note"/>
      </script></object></program>
      """
    When I request "GET" "/api/projects/1/code/translation?target_language=de"
    Then the response status code should be "202"
    When I request "GET" "/api/projects/1/code/translation?target_language=de&poll=true"
    Then the response status code should be "200"
    And the client response should contain "partial"
    When I request "GET" "/api/projects/1/code/translation?target_language=de&resume=true"
    Then the response status code should be "202"
    When I request "GET" "/api/projects/1/code/translation?target_language=de&poll=true"
    Then the response status code should be "200"
    And the client response should contain "translated Thirteenth note"
