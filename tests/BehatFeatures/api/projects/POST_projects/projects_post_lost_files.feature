@api @projects @upload
Feature: Re-uploading a project whose files were lost makes it public again

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name | owned by | has_missing_files |
      | 1  | test | Catrobat | true              |

  Scenario: The re-upload updates the project and shows it in public lists right away
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/?category=recent"
    Then the response status code should be "200"
    And the client response should not contain "test"
    Given I am "Catrobat"
    When I upload a valid Catrobat project
    Then the response status code should be "201"
    And the JSON response field "id" should be '"1"'
    Given I have a request header "HTTP_ACCEPT" with value "application/json"
    When I request "GET" "/api/projects/?category=recent"
    Then the response status code should be "200"
    And the client response should contain "test"
