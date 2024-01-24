@api @projects @post @tag
Feature: Upload a project with tag

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are tags:
      | id | internal_title | title_ltm_code |
      | 1  | Games          | Spiele         |
      | 2  | Story          | Geschichte     |
      | 3  | Music          | Musik          |
      | 4  | Art            | Kunst          |

  Scenario: upload a tagged program with tags Games and Story
    Given I have a project with "tags" set to "Games,Story"
    And I use the "english" app, API version 2
    When I upload this generated project, API version 2
    Then the project should be tagged with "Games,Story" in the database

  Scenario: upload a tagged program with tags Music and Art on a device with no supported language
    Given I have a project with "tags" set to "Music,Art"
    And I use the "unknownLanguage" app, API version 2
    When I upload this generated project, API version 2
    Then the project should be tagged with "Music,Art" in the database

  Scenario: upload a tagged program with unknown tags
    Given I have a project with "tags" set to "Huhu,dontKnow"
    And I use the "english" app, API version 2
    When I upload this generated project, API version 2
    Then the project should not be tagged

  Scenario: upload a tagged program with more then three tags
    Given I have a project with "tags" set to "Games,Story,Music,Art"
    And I use the "english" app, API version 2
    When I upload this generated project, API version 2
    Then the project should be tagged with "Games,Story,Music" in the database

  Scenario: update a program with other tags
    Given I have a project with "tags" set to "Music,Art"
    And I use the "english" app, API version 2
    And I upload this generated project, API version 2
    When I upload this generated project again with the tags "Games,Story", API version 2
    Then the project should be tagged with "Games,Story" in the database
