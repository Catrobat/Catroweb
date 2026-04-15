@api @media-library
Feature: Get media assets from the API

  Background:
    Given there are users:
      | id | name       | password |
      | 1  | AdminUser  | 123456   |
      | 2  | RegularUser| 123456   |
    And there are flavors:
      | id | name       |
      | 1  | pocketcode |
      | 2  | luna       |
      | 3  | embroidery |
      | 4  | arduino    |
    And there are media categories:
      | id                                   | name                          | priority |
      | 550e8400-e29b-41d4-a716-446655440001 | media_library.category.animals| 10       |
      | 550e8400-e29b-41d4-a716-446655440002 | media_library.category.sounds | 20       |
    And there are media assets:
      | id                                   | name              | extension | file_type | category                             | flavors              | downloads | author      |
      | 650e8400-e29b-41d4-a716-446655440001 | Dog Image         | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | pocketcode           | 100       | Bob Schmidt |
      | 650e8400-e29b-41d4-a716-446655440002 | Cat Image         | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | luna                 | 50        |             |
      | 650e8400-e29b-41d4-a716-446655440003 | Meow Sound        | mp3       | SOUND     | 550e8400-e29b-41d4-a716-446655440002 | pocketcode           | 25        |             |
      | 650e8400-e29b-41d4-a716-446655440004 | Neutral Asset     | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 |                      | 10        |             |
      | 650e8400-e29b-41d4-a716-446655440005 | Embroidery Only   | png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | embroidery           | 5         |             |
      | 650e8400-e29b-41d4-a716-446655440006 | Embroidery Arduino| png       | IMAGE     | 550e8400-e29b-41d4-a716-446655440001 | embroidery,arduino   | 3         |             |

  Scenario: Get all media assets
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets"
    Then the response status code should be "200"
    And the client response should contain "data"
    And the client response should contain "has_more"
    And the client response should contain "Dog Image"
    And the client response should contain "Cat Image"
    And the client response should contain "Meow Sound"

  Scenario: Get media assets by category
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?category_id=550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "200"
    And the client response should contain "Dog Image"
    And the client response should contain "Cat Image"
    And the client response should not contain "Meow Sound"

  Scenario: Get media assets by file type
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?file_type=SOUND"
    Then the response status code should be "200"
    And the client response should contain "Meow Sound"
    And the client response should not contain "Dog Image"

  Scenario: Get media assets by flavor
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?flavor=luna"
    Then the response status code should be "200"
    And the client response should contain "Cat Image"
    And the client response should not contain "Dog Image"
    And the client response should not contain "Meow Sound"

  Scenario: Search media assets by name
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?search=Dog"
    Then the response status code should be "200"
    And the client response should contain "Dog Image"
    And the client response should not contain "Cat Image"

  Scenario: Get media assets with invalid file type
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?file_type=VIDEO"
    Then the response status code should be "400"

  Scenario: Get specific project without accept header
    Given I have a request header "HTTP_ACCEPT" with value "invalid"
    And I request "GET" "/api/media/assets"
    Then the response status code should be "406"

  Scenario: Ranking by single active flavor - matching asset appears before neutral and non-matching
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?flavors=embroidery"
    Then the response status code should be "200"
    And the client response should contain "Embroidery Only"
    And the client response should contain "Embroidery Arduino"
    And the client response should contain "Neutral Asset"
    And the client response should contain "Dog Image"
    And the client response should contain "Embroidery Only" before "Neutral Asset"
    And the client response should contain "Embroidery Arduino" before "Neutral Asset"
    And the client response should contain "Neutral Asset" before "Dog Image"

  Scenario: Ranking by multiple active flavors - higher overlap ranks first
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?flavors=embroidery,arduino"
    Then the response status code should be "200"
    And the client response should contain "Embroidery Arduino" before "Embroidery Only"
    And the client response should contain "Embroidery Only" before "Neutral Asset"
    And the client response should contain "Neutral Asset" before "Dog Image"

  Scenario: Ranking combined with category filter
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?flavors=embroidery&category_id=550e8400-e29b-41d4-a716-446655440001"
    Then the response status code should be "200"
    And the client response should contain "Embroidery Only"
    And the client response should not contain "Meow Sound"
    And the client response should contain "Embroidery Only" before "Neutral Asset"

  Scenario: No active flavors - falls back to default ordering
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I request "GET" "/api/media/assets?flavors="
    Then the response status code should be "200"
    And the client response should contain "Dog Image"
    And the client response should contain "Cat Image"
