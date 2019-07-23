@api @upload @tag
Feature: Upload a program with tag

  Background:
    Given there are users:
      | name     | password | token      |
      | Catrobat | 12345    | cccccccccc |
    And there are programs:
      | id | name      | description | owned by | downloads | views | upload time      | version |
      | 1  | program 1 | p1          | Catrobat | 3         | 12    | 01.01.2013 12:00 | 0.8.5   |
    And there are tags:
      | id | en    | de         |
      | 1  | Games | Spiele     |
      | 2  | Story | Geschichte |
      | 3  | Music | Musik      |
      | 4  | Art   | Kunst      |

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

  Scenario: get all tags in english and statuscode 404 when no language is passed
    Given I want to upload a program
    And I have no parameters
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":404,
        "constantTags":[
                          "Games",
                          "Story",
                          "Music",
                          "Art"
                       ]
      }
      """

  Scenario: get all tags in english and statuscode 404 when a none existing language is passed
    Given I want to upload a program
    And I have a parameter "language" with value "NotExisting"
    When I GET the tag list from "/app/api/tags/getTags.json" with these parameters
    Then I should get the json object:
      """
      {
        "statusCode":404,
        "constantTags":[
                          "Games",
                          "Story",
                          "Music",
                          "Art"
                       ]
      }
      """

  Scenario: upload a tagged program with tags Games and Story on an english device
    Given I have a program with "Games,Story" as tags
    And I use the "english" app
    When I upload this program
    Then the program should be tagged with "Games,Story" in the database

  Scenario: upload a tagged program with tags Geschichte and Musik on a german device
    Given I have a program with "Geschichte,Musik" as tags
    And I use the "german" app
    When I upload this program
    Then the program should be tagged with "Geschichte,Musik" in the database

  Scenario: upload a tagged program with tags Music and Art on a device with no supported language
    Given I have a program with "Music,Art" as tags
    And I use the "unknownLangugae" app
    When I upload this program
    Then the program should be tagged with "Music,Art" in the database

  Scenario: upload a tagged program with unknown tags
    Given I have a program with "Huhu,dontKnow" as tags
    And I use the "english" app
    When I upload this program
    Then the program should not be tagged

  Scenario: upload a tagged program with more then three tags
    Given I have a program with "Games,Story,Music,Art" as tags
    And I use the "english" app
    When I upload this program
    Then the program should be tagged with "Games,Story,Music" in the database

  Scenario: update a program with other tags
    Given I have a program with "Music,Art" as tags
    And I use the "english" app
    And I upload this program
    When I upload this program again with the tags "Games,Story"
    Then the program should be tagged with "Games,Story" in the database
