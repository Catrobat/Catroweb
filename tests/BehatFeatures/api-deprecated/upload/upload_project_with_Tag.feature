@api @upload @tag
Feature: Upload a project with tag

  Background:
    Given there are users:
      | name     |
      | Catrobat |
    And there are tags:
      | id | internal_title | title_ltm_code |
      | 1  | Games          | Spiele         |
      | 2  | Story          | Geschichte     |
      | 3  | Music          | Musik          |
      | 4  | Art            | Kunst          |

  Scenario: get all tags for language english before upload
    Given I want to upload a project
    And I have a parameter "language" with value "de"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":200,
        "constantTags":[
                          "Games",
                          "Story",
                          "Music",
                          "Art"
                       ]
      }
      """

  Scenario: upload a tagged project with tags Games and Story
    Given I have a project with "tags" set to "Games,Story"
    And I use the "english" app, API version 1
    When I upload this generated project, API version 1
    Then the project should be tagged with "Games,Story" in the database

  Scenario: upload a tagged project with tags Music and Art on a device with no supported language
    Given I have a project with "tags" set to "Music,Art"
    And I use the "unknownLanguage" app, API version 1
    When I upload this generated project, API version 1
    Then the project should be tagged with "Music,Art" in the database

  Scenario: upload a tagged project with unknown tags
    Given I have a project with "tags" set to "Huhu,dontKnow"
    And I use the "english" app, API version 1
    When I upload this generated project, API version 1
    Then the project should not be tagged

  Scenario: upload a tagged project with more then three tags
    Given I have a project with "tags" set to "Games,Story,Music,Art"
    And I use the "english" app, API version 1
    When I upload this generated project, API version 1
    Then the project should be tagged with "Games,Story,Music" in the database

  Scenario: update a project with other tags
    Given I have a project with "tags" set to "Music,Art"
    And I use the "english" app, API version 1
    And I upload this generated project, API version 1
    When I upload this generated project again with the tags "Games,Story", API version 1
    Then the project should be tagged with "Games,Story" in the database
