@api @studio
Feature: Creating a new studio

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
    And there are studios:
      | id | name        | description   |
      | 1  | Cool Studio | A cool studio |

  Scenario: An request with missing jwt token should result in an error
    When I request "POST" "/api/studios"
    Then the response status code should be "401"

  Scenario: An request with an invalid jwt token should result in an error
    Given I use an invalid JWT Bearer token
    And I request "POST" "/api/studios"
    Then the response status code should be "401"

  Scenario: Empty request fields should result in an error for all required fields
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "name", "message": "Name missing"},
            {"field": "description", "message": "Description missing"}
          ]
        }
      }
    """

  Scenario: Creating a studio with a name that's too short should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value |
      | name        | <3    |
      | description | -     |
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "name", "message": "Name too short"}
          ]
        }
      }
    """

  Scenario: Creating a studio with a name and description that are too long should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | name        | more than 180: ----------------------------------------------------------------------------------------------------------------------------------------------------------------------                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | description | more than 3000: --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "name", "message": "Name too long"},
            {"field": "description", "message": "Description too long"}
          ]
        }
      }
    """

  Scenario: Trying to create a studio with an already existing name should result in an error
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value            |
      | name        | Cool Studio      |
      | description | with description |
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "name", "message": "Name already in use"}
          ]
        }
      }
    """

  Scenario: Creating a studio with only required fields should succeed
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value              |
      | name        | New default Studio |
      | description | with description   |
    And I request "POST" "/api/studios"
    Then the response status code should be "201"
    And I should get the json object:
    """
      {
        "id": "REGEX_STRING_WILDCARD",
        "name": "New default Studio",
        "description": "with description",
        "is_public": true,
        "enable_comments": true,
        "cover": null,
        "members_count": 1,
        "projects_count": 0,
        "activities_count": 1,
        "comments_count": 0
      }
    """

  Scenario: Creating a studio with all fields should succeed
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name            | value            |
      | name            | New Studio       |
      | description     | with description |
      | is_public       | false            |
      | enable_comments | false            |
    And I add the file "galaxy.jpg" from path "Studio" as "image_file"
    And I request "POST" "/api/studios"
    Then the response status code should be "201"
    And I should get the json object:
    """
      {
        "id": "REGEX_STRING_WILDCARD",
        "name": "New Studio",
        "description": "with description",
        "is_public": false,
        "enable_comments": false,
        "cover": {
          "thumb": {"avif_1x": "REGEX_STRING_WILDCARD", "avif_2x": "REGEX_STRING_WILDCARD", "webp_1x": "REGEX_STRING_WILDCARD", "webp_2x": "REGEX_STRING_WILDCARD"},
          "card": {"avif_1x": "REGEX_STRING_WILDCARD", "avif_2x": "REGEX_STRING_WILDCARD", "webp_1x": "REGEX_STRING_WILDCARD", "webp_2x": "REGEX_STRING_WILDCARD"},
          "detail": {"avif_1x": "REGEX_STRING_WILDCARD", "avif_2x": "REGEX_STRING_WILDCARD", "webp_1x": "REGEX_STRING_WILDCARD", "webp_2x": "REGEX_STRING_WILDCARD"},
          "width": null,
          "height": null
        },
        "members_count": 1,
        "projects_count": 0,
        "activities_count": 1,
        "comments_count": 0
      }
    """

  Scenario: Only allow valid images as file upload
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name            | value            |
      | name            | New Studio       |
      | description     | with description |
    And I add the file "corrupt.jpg" from path "Studio" as "image_file"
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "image_file", "message": "Image file invalid"}
          ]
        }
      }
    """

  Scenario: Only allow supported image types as file upload
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name            | value            |
      | name            | New Studio       |
      | description     | with description |
    And I add the file "galaxy.tif" from path "Studio" as "image_file"
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "image_file", "message": "Image file type invalid"}
          ]
        }
      }
    """

  Scenario: Only allow supported image types as file upload
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name            | value            |
      | name            | New Studio       |
      | description     | with description |
    And I add the file "galaxy-too-large.jpg" from path "Studio" as "image_file"
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "image_file", "message": "Image file too large"}
          ]
        }
      }
    """

  Scenario: Creating a studio with valid fields and Accept-Language header set to German should return a German response
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I have the POST parameters:
      | name | value      |
      | name | New Studio |
    And I request "POST" "/api/studios"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "error": {
          "code": 422,
          "type": "validation_error",
          "message": "Validation failed",
          "details": [
            {"field": "description", "message": "Beschreibung fehlt"}
          ]
        }
      }
    """
