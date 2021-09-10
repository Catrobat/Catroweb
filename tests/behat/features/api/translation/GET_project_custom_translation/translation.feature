@api
Feature: Server returns existing custom translation
  Existing custom translation should not be changed

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description   | credit   |
      | 1  | project1 | Catrobat | mydescription | mycredit |

  Scenario Outline: Anyone can retrieve translation
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    When I request "GET" "/app/translate/custom/project/1?field=<field>&language=fr"
    Then the response status code should be "200"
    And the client response should contain "translated <field>"
    And there should be project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario Outline: Anyone can check whether translation exists
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    When I request "GET" "/app/translate/custom/project/1?field=<field>&language=fr"
    Then the response status code should be "200"
    When I request "GET" "/app/translate/custom/project/1?field=<field>&language=en"
    Then the response status code should be "404"
    When I request "GET" "/app/translate/custom/project/2?field=<field>&language=fr"
    Then the response status code should be "404"
    And there should be project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario Outline: Translation doesn't exist for non existent project
    When I request "GET" "/app/translate/custom/project/2?field=<field>&language=fr"
    Then the response status code should be "404"

    Examples:
      | field       |
      | name        |
      | description |
      | credit      |

  Scenario: Return error when invalid field name is used
    Given there are project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
    When I request "GET" "/app/translate/custom/project/1?field=other&language=fr"
    Then the response status code should be "400"
    And there should be project custom translations:
      | project_id | language | name            | description            | credit            |
      | 1          | fr       | translated name | translated description | translated credit |
