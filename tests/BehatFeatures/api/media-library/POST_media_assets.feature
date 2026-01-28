Feature: Upload media assets

  Background:
    Given there are admins:
      | name  | password | email                | id | role             |
      | Admin | 123456   | admin@pocketcode.org | 1  | ROLE_MEDIA_ADMIN |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
    And there are media categories:
      | id                                   | name                           | description                         | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals | media_library.category.animals_desc | 10       |

  Scenario: Upload a media asset
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value                                |
      | name        | Dog Image                            |
      | description | A dog image                          |
      | category_id | 550e8400-e29b-41d4-a716-446655440001 |
      | flavors[0]  | pocketcode                           |
      | author      | Catrobat                             |
    And I add the file "galaxy.jpg" from path "Studio" as "file"
    And I request "POST" "/api/media/assets"
    Then the response status code should be "201"
    And the client response should contain "Dog Image"

  Scenario: Upload a media asset without a file
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value                                |
      | name        | Dog Image                            |
      | category_id | 550e8400-e29b-41d4-a716-446655440001 |
      | flavors[0]  | pocketcode                           |
    And I request "POST" "/api/media/assets"
    Then the response status code should be "400"

  Scenario: Upload a media asset with invalid file type
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value                                |
      | name        | Dog Image                            |
      | category_id | 550e8400-e29b-41d4-a716-446655440001 |
      | flavors[0]  | pocketcode                           |
    And I add the file "galaxy.tif" from path "Studio" as "file"
    And I request "POST" "/api/media/assets"
    Then the response status code should be "422"

  Scenario: Upload a media asset with invalid accept header
    Given I use a valid JWT Bearer token for "Admin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "invalid"
    And I have the POST parameters:
      | name        | value                                |
      | name        | Dog Image                            |
      | category_id | 550e8400-e29b-41d4-a716-446655440001 |
      | flavors[0]  | pocketcode                           |
    And I add the file "galaxy.jpg" from path "Studio" as "file"
    And I request "POST" "/api/media/assets"
    Then the response status code should be "406"

  Scenario: Uploading a media asset without authentication fails
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value                                |
      | name        | Dog Image                            |
      | category_id | 550e8400-e29b-41d4-a716-446655440001 |
      | flavors[0]  | pocketcode                           |
    And I add the file "galaxy.jpg" from path "Studio" as "file"
    And I request "POST" "/api/media/assets"
    Then the response status code should be "401"
