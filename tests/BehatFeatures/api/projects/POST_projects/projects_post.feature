@api @projects @post
Feature: Uploading a project

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |

  Scenario: An request with missing jwt token should result in an error
    And I request "POST" "/api/projects"
    Then the response status code should be "401"

  Scenario: An request with missing mandatory checksum parameter should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "file" with value "file"
    And I request "POST" "/api/projects"
    Then the response status code should be "400"

  Scenario: An request with missing mandatory file parameter should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "checksum" with value "checksum"
    And I request "POST" "/api/projects"
    Then the response status code should be "400"

  Scenario: An request with an invalid flavor parameter should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "flavor" with value "pocketcooode"
    And I request "POST" "/api/projects"
    Then the response status code should be "400"

  Scenario: An request with an invalid value of the private parameter should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "private" with value "faaalse"
    And I request "POST" "/api/projects"
    Then the response status code should be "400"

  Scenario: A request with an invalid value of the file parameter should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "checksum" with value "checksum"
    And I have a parameter "checksum" with value "text"
    And I request "POST" "/api/projects"
    Then the response status code should be "400"

  Scenario: A mismatch between the checksum of the uploaded file and the checksum parameter should result in an UploadError
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "checksum" with value "wrong_checksum"
    And I have a broken Catrobat file
    And I request "POST" "/api/projects"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Invalid checksum. Try uploading again!"
      }
    """

  Scenario: Uploading a broken .catrobat file should result in an UploadError
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "checksum" with value "5A136BCF4179C875F61BD7505A1A63F6"
    And I have a broken Catrobat file
    And I request "POST" "/api/projects"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Error while creating project entity. Try uploading again!"
      }
    """

  Scenario: Uploading a broken .catrobat file should result in an UploadError (w/ Accept-Language header set to German)
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I have a parameter "checksum" with value "5A136BCF4179C875F61BD7505A1A63F6"
    And I have a broken Catrobat file
    And I request "POST" "/api/projects"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": "Fehler während dem Erstellen der Program Entity. Bitte versuche es erneut!"
      }
    """

  Scenario: Uploading a project should work
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a parameter "checksum" with value "B472E2CB01AEACE0F359D0A1FE9A4036"
    And I have a valid Catrobat file
    And I request "POST" "/api/projects"
    Then the response status code should be "201"
    And the uploaded project should exist in the database
    And the response should contain the URL of the uploaded project

  Scenario: uploading the same project again should result in an update
    Given I am "Catrobat"
    When I upload a valid Catrobat project
    And I upload a valid Catrobat project with the same name
    Then the uploaded project should exist in the database
    And it should be updated
