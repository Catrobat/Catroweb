@api
Feature: Server stores new custom translation

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | Oscar    | 123456   |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |

  Scenario Outline: Project owner can set translation
    Given there are project custom translations:
      | project_id | language | name | description | credit |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"translated"}
      """
    When I request "PUT" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "200"
    And there should be project custom translations:
      | project_id | language | name   | description   | credit   |
      | 1          | fr       | <name> | <description> | <credit> |

    Examples:
      | field       | name       | description | credit     |
      | name        | translated |             |            |
      | description |            | translated  |            |
      | credit      |            |             | translated |

  Scenario Outline: Project owner can update translation
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"updated"}
      """
    When I request "PUT" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "200"
    And there should be project custom translations:
      | project_id | language | name   | description   | credit   |
      | 1          | fr       | <name> | <description> | <credit> |

    Examples:
      | field       | name            | description            | credit            |
      | name        | updated         | translated description | translated credit |
      | description | translated name | updated                | translated credit |
      | credit      | translated name | translated description | updated           |

  Scenario Outline: Custom translation cannot be changed when the user doesn't own the project
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I use a valid JWT Bearer token for "Oscar"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"updated"}
      """
    When I request "PUT" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "404"
    And there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario Outline: Custom translation cannot be changed when not logged in
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"updated"}
      """
    When I request "PUT" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "401"
    And there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario Outline: Custom translation cannot be set to empty, use DELETE instead
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":""}
      """
    When I request "PUT" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "400"
    And there should be project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario: Return error when invalid field name is used
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
      """
      {"text":"new"}
      """
    When I request "PUT" "/api/projects/1/translation/other/fr"
    Then the response status code should be "400"
    And there should be project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
