@api @projects @put
Feature: Set a custom URL slug for a project

  Background:
    Given there are users:
      | name     |
      | Catrobat |
      | Other    |
    And there are projects:
      | id | name        | owned by | slug       |
      | 1  | Project One | Catrobat |            |
      | 2  | Project Two | Other    | taken-slug |

  Scenario: Owner sets a slug; it is stored lowercase
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"slug": "My-Cool-Game"}
    """
    And I request "PATCH" "/api/projects/1"
    Then the response code should be "204"
    And the following projects exist in the database:
      | id | slug         |
      | 1  | my-cool-game |

  Scenario: An empty slug removes the custom URL
    Given I use a valid JWT Bearer token for "Other"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"slug": ""}
    """
    And I request "PATCH" "/api/projects/2"
    Then the response code should be "204"
    And the following projects exist in the database:
      | id | slug |
      | 2  |      |

  Scenario: A slug taken by another project is rejected
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"slug": "taken-slug"}
    """
    And I request "PATCH" "/api/projects/1"
    Then the response code should be "422"
    And the client response should contain "This custom URL is already taken"

  Scenario: Reserved and malformed slugs are rejected
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"slug": "upload"}
    """
    And I request "PATCH" "/api/projects/1"
    Then the response code should be "422"
    And the client response should contain "This custom URL is reserved"

  Scenario: A UUID-shaped slug is rejected
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"slug": "3d49a458-4421-42e9-9fdc-804dfb6ad342"}
    """
    And I request "PATCH" "/api/projects/1"
    Then the response code should be "422"
    And the client response should contain "Custom URL must be 3-50"

  Scenario: Only the owner can set a slug
    Given I use a valid JWT Bearer token for "Other"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"slug": "stolen-name"}
    """
    And I request "PATCH" "/api/projects/1"
    Then the response code should be "403"
