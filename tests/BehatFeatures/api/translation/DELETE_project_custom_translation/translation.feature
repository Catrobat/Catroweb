@api
Feature: Server deletes custom translation

  Background:
    Given there are users:
      | id | name     | password |
      | 1  | Catrobat | 123456   |
      | 2  | Oscar    | 123456   |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |

  Scenario Outline: Project owner can delete translation
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I use a valid JWT Bearer token for "Catrobat"
    When I request "DELETE" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "204"
    And there should be project custom translations:
      | project_id | language | name   | description   | credit   |
      | 1          | fr       | <name> | <description> | <credit> |

    Examples:
      | field       | name            | description            | credit             |
      | name        |                 | translated description | translated credit |
      | description | translated name |                        | translated credit  |
      | credit      | translated name | translated description |                    |

  Scenario Outline: Delete custom translation entry when name, description and credit are empty
    Given there are project custom translations:
      | project_id | language | name   | description   | credit   |
      | 1          | fr       | <name> | <description> | <credit> |
    And I use a valid JWT Bearer token for "Catrobat"
    When I request "DELETE" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "204"
    And there should be project custom translations:
      | project_id | language | name | description | credit |

    Examples:
      | field       | name            | description            | credit            |
      | name        | translated name |                        |                   |
      | description |                 | translated description |                   |
      | credit      |                 |                        | translated credit |

  Scenario Outline: Custom translation cannot be deleted when the user doesn't own the project
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    And I use a valid JWT Bearer token for "Oscar"
    When I request "DELETE" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "404"
    And there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario Outline: Custom translation cannot be deleted when not logged in
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    When I request "DELETE" "/api/projects/1/translation/<field>/fr"
    Then the response status code should be "401"
    And there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |
