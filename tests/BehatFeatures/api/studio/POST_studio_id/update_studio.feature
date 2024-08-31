@api @studio
Feature: Updating an existing studio

  Background:
    Given there are users:
      | id | name        | password |
      | 1  | StudioAdmin | 123456   |
      | 2  | Member      | 123456   |
    And there are studios:
      | id | name           | description           |
      | 1  | Cool Studio    | cool description      |
      | 2  | Another Studio | nothing to see here.. |
    And there are studio users:
      | id | user        | studio_id | role   |
      | 1  | StudioAdmin | 1         | admin  |
      | 2  | Member      | 1         | member |

  Scenario: An request with missing jwt token should result in an error
    When I request "POST" "/api/studio/1"
    Then the response status code should be "401"

  Scenario: An request with an invalid jwt token should result in an error
    Given I use an invalid JWT Bearer token
    And I request "POST" "/api/studio/1"
    Then the response status code should be "401"

  Scenario: Updating a studio with a name that's too short should result in an error
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value |
      | name        | <3    |
      | description | -     |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "name": "Name too short"
      }
    """

  Scenario: Updating a studio with a name and description that are too long should result in an error
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | name        | more than 180: ----------------------------------------------------------------------------------------------------------------------------------------------------------------------                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | description | more than 3000: --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "name": "Name too long",
        "description": "Description too long"
      }
    """

  Scenario: Trying to update a studio with an already existing name should result in an error
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value            |
      | name        | Another studio   |
      | description | with description |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "name": "Name already in use"
      }
    """

  Scenario: Only Admins are allowed to update a studio
    Given I use a valid JWT Bearer token for "Member"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value               |
      | description | Updated description |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "403"

  Scenario: Updating a studio with a single valid fields should succeed
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value               |
      | description | Updated description |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Cool Studio",
        "description": "Updated description",
        "is_public": true,
        "enable_comments": true,
        "image_path": ""
      }
    """

  Scenario: Updating a studio with multiple valid fields should succeed
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value               |
      | name        | Updated Studio      |
      | description | Updated description |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Updated Studio",
        "description": "Updated description",
        "is_public": true,
        "enable_comments": true,
        "image_path": ""
      }
    """

  Scenario: Updating a studio with the current name should do nothing
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name | value       |
      | name | Cool Studio |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Cool Studio",
        "description": "cool description",
        "is_public": true,
        "enable_comments": true,
        "image_path": ""
      }
    """

  Scenario: Updating a studio with all fields should succeed
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name            | value               |
      | name            | Updated Studio      |
      | description     | Updated description |
      | is_public       | false               |
      | enable_comments | false               |
    And I add the file "galaxy.jpg" from path "Studio" as "image_file"
    And I request "POST" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Updated Studio",
        "description": "Updated description",
        "is_public": false,
        "enable_comments": false,
        "image_path": "REGEX_STRING_WILDCARDUpdated-Studio.jpg"
      }
    """

  Scenario: Updating an image should delete the old image from the server
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I add the file "galaxy.jpg" from path "Studio" as "image_file"
    And the current time is "30.08.2024 12:00"
    And I request "POST" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Cool Studio",
        "description": "cool description",
        "is_public": true,
        "enable_comments": true,
        "image_path": "REGEX_STRING_WILDCARD1725019200-Cool-Studio.jpg"
      }
    """
    And the uploaded file "resources/images/studio/1725019200-Cool-Studio.jpg" exists
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I add the file "galaxy.jpg" from path "Studio" as "image_file"
    And the current time is "30.08.2025 13:00"
    And I request "POST" "/api/studio/1"
    Then the response status code should be "200"
    And I should get the json object:
    """
      {
        "id": "1",
        "name": "Cool Studio",
        "description": "cool description",
        "is_public": true,
        "enable_comments": true,
        "image_path": "REGEX_STRING_WILDCARD1756558800-Cool-Studio.jpg"
      }
    """
    And the uploaded file "resources/images/studio/1725019200-Cool-Studio.jpg" does not exist
    And the uploaded file "resources/images/studio/1756558800-Cool-Studio.jpg" exists

  Scenario: Only allow valid images as file upload
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value            |
      | name        | New Studio       |
      | description | with description |
    And I add the file "corrupt.jpg" from path "Studio" as "image_file"
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "image_file": "Image file invalid"
      }
    """

  Scenario: Only allow supported image types as file upload
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value            |
      | name        | New Studio       |
      | description | with description |
    And I add the file "galaxy.tif" from path "Studio" as "image_file"
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "image_file": "Image file type invalid"
      }
    """

  Scenario: Only allow supported image types as file upload
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value            |
      | name        | New Studio       |
      | description | with description |
    And I add the file "galaxy-too-large.jpg" from path "Studio" as "image_file"
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "image_file": "Image file too large"
      }
    """

  Scenario: Updating a studio with valid fields and Accept-Language header set to German should return a German response
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "HTTP_ACCEPT_LANGUAGE" with value "de_DE"
    And I have the POST parameters:
      | name | value                                                                                                                                                                                 |
      | name | more than 180: ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
    And I request "POST" "/api/studio/1"
    Then the response status code should be "422"
    And I should get the json object:
    """
      {
        "name": "Name zu lang"
      }
    """
