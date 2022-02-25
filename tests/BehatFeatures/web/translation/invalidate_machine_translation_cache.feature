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
    And I POST login with user "Catrobat" and password "123456"
    When I have a request parameter "value" with value "new value"
    And I request "PUT" "/app/<url>/1"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 16          |

    Examples:
      | url                    |
      | editProjectName        |
      | editProjectDescription |
      | editProjectCredits     |

  Scenario Outline: Edits should not invalidate cached translation when not logged in
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    When I have a request parameter "value" with value "new value"
    And I request "PUT" "/app/<url>/1"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |

    Examples:
      | url                    |
      | editProjectName        |
      | editProjectDescription |
      | editProjectCredits     |

  Scenario Outline: Nothing happens when other project properties are changed
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    And I log in as "<user>"
    When I go to "<url>"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |

    Examples:
      | user     | url                            |
      | Catroweb | /app/userDeleteProject/1           |
      | Catroweb | /app/userToggleProjectVisibility/1 |
      | Alice    | /app/project/like/1                |

  Scenario Outline: Nothing happens when other projects are edited
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |
    And I POST login with user "Catrobat" and password "123456"
    When I have a request parameter "value" with value "new value"
    And I request "PUT" "/app/<url>/2"
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 1          | en              | fr-FR           | itranslate | 16          | translated project1 | translated description1 | translated credit1 |

    Examples:
      | url                    |
      | editProjectName        |
      | editProjectDescription |
      | editProjectCredits     |
