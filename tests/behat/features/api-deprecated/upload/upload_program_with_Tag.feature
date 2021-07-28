@api @upload @tag
Feature: Upload a program with tag

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
    Given I want to upload a program
    And I have a parameter "language" with value "de"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":200,
        "constantTags":[
                          "Spiele",
                          "Geschichte",
                          "Musik",
                          "Kunst"
                       ]
      }
      """

  Scenario: upload a tagged program with tags Games and Story
    Given I have a program with "tags" set to "Games,Story"
    And I use the "english" app, API version 1
    When I upload this generated program, API version 1
    Then the program should be tagged with "Games,Story" in the database

  Scenario: upload a tagged program with tags Music and Art on a device with no supported language
    Given I have a program with "tags" set to "Music,Art"
    And I use the "unknownLanguage" app, API version 1
    When I upload this generated program, API version 1
    Then the program should be tagged with "Music,Art" in the database

  Scenario: upload a tagged program with unknown tags
    Given I have a program with "tags" set to "Huhu,dontKnow"
    And I use the "english" app, API version 1
    When I upload this generated program, API version 1
    Then the program should not be tagged

  Scenario: upload a tagged program with more then three tags
    Given I have a program with "tags" set to "Games,Story,Music,Art"
    And I use the "english" app, API version 1
    When I upload this generated program, API version 1
    Then the program should be tagged with "Games,Story,Music" in the database

  Scenario: update a program with other tags
    Given I have a program with "tags" set to "Music,Art"
    And I use the "english" app, API version 1
    And I upload this generated program, API version 1
    When I upload this generated program again with the tags "Games,Story", API version 1
    Then the program should be tagged with "Games,Story" in the database
