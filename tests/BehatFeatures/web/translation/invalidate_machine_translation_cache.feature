@web
Feature: Invalidate project cached machine translation

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
      | 2  | Alice    |
    And there are projects:
      | id | name     | owned by | description  | credit  |
      | 1  | project1 | Catrobat | description1 | credit1 |
      | 2  | project2 | Catrobat | description2 | credit2 |

  Scenario Outline: Invalidate cached translation when project is edited
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
        """
          {
            "<property>": "new random value"
          }
        """
    When I request "PUT" "/api/project/1"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 16          |

    Examples:
      | property    |
      | name        |
      | description |
      | credits      |

  Scenario Outline: Edits should not invalidate cached translation when not logged in
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
        """
          {
            "<property>": "new value"
          }
        """
    When I request "PUT" "/api/project/1"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |

    Examples:
      | property    |
      | name        |
      | description |
      | credits     |

  Scenario Outline: Nothing happens when other project properties are changed
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    And I use a valid JWT Bearer token for "<user>"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
        """
          {
            "<property>": <value>
          }
        """
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |

    Examples:
      | user     | property | value |
      | Catroweb | visible  | 0     |
      | Catroweb | private  | 1     |

  Scenario Outline: Nothing happens when other projects are edited
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    And I use a valid JWT Bearer token for "Catrobat"
    And I have a request header "HTTP_ACCEPT" with value "application/json"
    And I have a request header "CONTENT_TYPE" with value "application/json"
    And I have the following JSON request body:
        """
          {
            "<property>": "new value"
          }
        """
    When I request "PUT" "/api/project/2"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |

    Examples:
      | property    |
      | name        |
      | description |
      | credits     |