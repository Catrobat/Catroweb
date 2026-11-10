@api @projects @post
Feature: Resumable project upload

  Project files larger than one request may carry are started, sent in chunks and completed.

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And I am "Catrobat"

  Scenario: A project sent in chunks is created like a single-request upload
    When I upload a valid Catrobat project in chunks
    Then the response status code should be "201"
    And the client response should contain "\"name\":\"test\""

  Scenario: Completing an upload again answers with the project it created
    When I upload a valid Catrobat project in chunks
    And I complete the saved upload
    Then the response status code should be "201"
    And the client response should contain "\"name\":\"test\""

  Scenario: Starting an upload needs a login
    Given I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"file_size": 1000}
      """
    When I request "POST" "/api/projects/uploads"
    Then the response status code should be "401"

  Scenario: A file larger than the server accepts is refused before any chunk is sent
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
      """
      {"file_size": 99999999999}
      """
    When I request "POST" "/api/projects/uploads"
    Then the response status code should be "422"
    And the client response should contain "file_too_large"

  Scenario: Completing an upload that is missing chunks is refused
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the following JSON request body:
      """
      {"file_size": 1000}
      """
    And I request "POST" "/api/projects/uploads"
    And I save the upload id from the response
    When I complete the saved upload
    Then the response status code should be "422"
    And the client response should contain "chunks_missing"

  Scenario: Someone else's upload is not found
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/uploads/0123456789abcdef0123456789abcdef"
    Then the response status code should be "404"
