@web
Feature: Cache project machine translations

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by | description  | credit  |
      | 1  | project1 | Catrobat |              |         |
      | 3  | project3 | Catrobat | description3 | credit3 |
      | 4  | project4 | Catrobat | description4 | credit4 |
    And there are comments:
      | id                                   | project_id | user_id | text |
      | 00000000-0000-0000-0000-000000000001 | 1          | 1       | c1   |

  Scenario: The first translation of a project is cached under the detected source language
    Given I switch the language to "French"
    And I am on "/app/project/3"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the "#name-translation" element should contain "translated project3"
    And there should be project machine translations:
      | project_id | source_language | target_language | cached_name         | cached_description      | cached_credits     |
      | 3          | en              | fr-FR           | translated project3 | translated description3 | translated credit3 |

  Scenario: A cache entry cleared by a project edit is filled again
    Given there are project machine translations:
      | project_id | source_language | target_language | cached_name | cached_description | cached_credits |
      | 4          | en              | fr-FR           |             |                    |                |
    And I switch the language to "French"
    And I am on "/app/project/4"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then there should be project machine translations:
      | project_id | source_language | target_language | cached_name         | cached_description      | cached_credits     |
      | 4          | en              | fr-FR           | translated project4 | translated description4 | translated credit4 |

  Scenario: A site language the translation service lacks is translated into English
    Given I switch the language to "Italian"
    And I am on "/app/project/1"
    And I wait for the page to be loaded
    When I click "#project-translation-button"
    And I wait for AJAX to finish
    Then the "#name-translation" element should contain "translated project1"
    And there should be project machine translations:
      | project_id | source_language | target_language | cached_name         | cached_description | cached_credits |
      | 1          | en              | en              | translated project1 |                    |                |

  Scenario: Comment translations are not cached
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    And I wait for AJAX to finish
    And I wait for the element "#comment-translation-button-00000000-0000-0000-0000-000000000001" to be visible
    When I click "#comment-translation-button-00000000-0000-0000-0000-000000000001"
    And I wait for AJAX to finish
    Then the "#comment-text-translation-00000000-0000-0000-0000-000000000001" element should contain "translated c1"
    And there should be project machine translations:
      | project_id | source_language | target_language | cached_name | cached_description | cached_credits |
