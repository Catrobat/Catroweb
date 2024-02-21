@web
Feature: Persist project and comment machine translation

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description  | credit  |
      | 1  | project1 | Catrobat |              |         |
      | 2  | project2 | Catrobat |              |         |
      | 3  | project3 | Catrobat | description3 | credit3 |
    And there are comments:
      | id | project_id | user_id | text |
      | 1  | 2          | 1       | c1   |
      | 2  | 1          | 1       | c2   |

  Scenario: Create new entry the first time a project is translated
    Given there are project machine translations:
      | project_id | source_language | target_language | provider | usage_count |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 1           |

  Scenario: Increment usage count if project entry already exists
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 2           |
      | 2          | en              | fr-FR           | itranslate | 1           |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 3           |
      | 2          | en              | fr-FR           | itranslate | 1           |

  Scenario: Cache project translation if used often
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count |
      | 3          | en              | fr-FR           | itranslate | 15          |
    And I switch the language to "French"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |

  Scenario: Create new entry for database cached project translation
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |
    And I switch the language to "French"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |
      | 3          | en              | fr-FR           | db         | 1           |                     |                         |                    |

  Scenario: Increment usage count for database cached project translation
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |
      | 3          | en              | fr-FR           | db         | 1           |                     |                         |                    |
    And I switch the language to "French"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |
      | 3          | en              | fr-FR           | db         | 2           |                     |                         |                    |

  Scenario: Do not cache "cached" project translation
    Given there are project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |
      | 3          | en              | fr-FR           | db         | 16          |                     |                         |                    |
    And I switch the language to "French"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | provider   | usage_count | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | itranslate | 16          | translated project3 | translated description3 | translated credit3 |
      | 3          | en              | fr-FR           | db         | 17          |                     |                         |                    |

  Scenario: Create new entry the first time a comment is translated
    Given there are comment machine translations:
      | comment_id | source_language | target_language | provider | usage_count |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-2"
    And I wait for AJAX to finish
    Then there should be comment machine translations:
      | comment_id | source_language | target_language | provider   | usage_count |
      | 2          | en              | fr-FR           | itranslate | 1           |

  Scenario: Increment usage count if comment entry already exists
    Given there are comment machine translations:
      | comment_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 1           |
      | 2          | en              | fr-FR           | itranslate | 1           |
    And I switch the language to "French"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#comment-translation-button-2"
    And I wait for AJAX to finish
    Then there should be comment machine translations:
      | comment_id | source_language | target_language | provider   | usage_count |
      | 1          | en              | fr-FR           | itranslate | 1           |
      | 2          | en              | fr-FR           | itranslate | 2           |

  Scenario: Create new entry for etag cached response for project without source language
    Given there are project machine translations:
      | project_id | source_language | target_language | provider | usage_count |
    And I have a request header "HTTP_IF_NONE_MATCH" with value '"eeb28b694e178d22952bf92a025a892cfr"'
    When I request "GET" "/app/translate/project/1?target_language=fr"
    Then the response status code should be "304"
    And there should be project machine translations:
      | project_id | source_language | target_language | provider | usage_count |
      | 1          |                 | fr              | etag     | 1           |

  Scenario: Increment usage count if etag cached response for project already exists
    Given there are project machine translations:
      | project_id | source_language | target_language | provider | usage_count |
      | 1          |                 | fr              | etag     | 2           |
      | 2          |                 | fr              | etag     | 1           |
    And I have a request header "HTTP_IF_NONE_MATCH" with value '"eeb28b694e178d22952bf92a025a892cfr"'
    When I request "GET" "/app/translate/project/1?target_language=fr"
    Then the response status code should be "304"
    And there should be project machine translations:
      | project_id | source_language | target_language | provider | usage_count |
      | 1          |                 | fr              | etag     | 3           |
      | 2          |                 | fr              | etag     | 1           |

  Scenario: Create new entry for etag cached response for comment without source language
    Given there are comment machine translations:
      | comment_id | source_language | target_language | provider | usage_count |
    And I have a request header "HTTP_IF_NONE_MATCH" with value '"a9f7e97965d6cf799a529102a973b8b9fr"'
    When I request "GET" "/app/translate/comment/1?target_language=fr"
    Then the response status code should be "304"
    And there should be comment machine translations:
      | comment_id | source_language | target_language | provider | usage_count |
      | 1          |                 | fr              | etag     | 1           |

  Scenario: Increment usage count if etag cached response for comment already exists
    Given there are comment machine translations:
      | comment_id | source_language | target_language | provider | usage_count |
      | 1          |                 | fr              | etag     | 2           |
      | 2          |                 | fr              | etag     | 1           |
    And I have a request header "HTTP_IF_NONE_MATCH" with value '"a9f7e97965d6cf799a529102a973b8b9fr"'
    When I request "GET" "/app/translate/comment/1?target_language=fr"
    Then the response status code should be "304"
    And there should be comment machine translations:
      | comment_id | source_language | target_language | provider | usage_count |
      | 1          |                 | fr              | etag     | 3           |
      | 2          |                 | fr              | etag     | 1           |
