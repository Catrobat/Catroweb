@api @moderation
Feature: Texts that say the content is for adults hide it until an admin reviews it

  Background:
    Given there are users:
      | id | name        | password |
      | 1  | Catrobat    | 123456   |
      | 2  | StudioAdmin | 123456   |
    And there are projects:
      | id | name        | description   | owned by |
      | 1  | Project One | First project | Catrobat |
    And there are studios:
      | id | name        | description      |
      | 1  | Cool Studio | cool description |
    And there are studio users:
      | id | user        | studio_id | role  |
      | 1  | StudioAdmin | 1         | admin |

  Scenario: A project description with an adults-only signal is saved and hidden for review
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"description": "Contains 18+ content"}
    """
    When I request "PATCH" "/api/projects/1"
    Then the response code should be "204"
    And the following projects exist in the database:
      | id | description          |
      | 1  | Contains 18+ content |
    And the project "1" should be hidden

  Scenario: A version number is not an adults-only signal
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"description": "Needs Pocket Code 1.18+ or version 18+"}
    """
    When I request "PATCH" "/api/projects/1"
    Then the response code should be "204"
    And the project "1" should be visible

  Scenario: A studio description with an adults-only signal hides the studio for review
    Given I use a valid JWT Bearer token for "StudioAdmin"
    And I have a request header "CONTENT_TYPE" with value "multipart/form-data"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have the POST parameters:
      | name        | value         |
      | description | +18 projects  |
    When I request "PATCH" "/api/studios/1"
    Then the response status code should be "200"
    And the studio "1" should be hidden

  Scenario: An about text with an adults-only signal is cleared and queued, the account stays usable
    Given I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
    """
      {"about": "18+ only"}
    """
    When I request "PATCH" "/api/users/me"
    Then the response code should be "204"
    And the user "Catrobat" profile should be visible
    And the project "1" should be visible
    When I request "GET" "/api/users/me"
    Then the response code should be "200"
