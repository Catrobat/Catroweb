@api @projects @post @tag
Feature: Upload a project with tag

  Background:
    Given there are users:
    | id  | name     | password |
    | 1   | Catrobat | 12345    |
    And there are tags:
    | id | en    | de         |
    | 1  | Games | Spiele     |
    | 2  | Story | Geschichte |
    | 3  | Music | Musik      |
    | 4  | Art   | Kunst      |


  Scenario: upload a tagged program with tags Games and Story on an english device
    Given I have a program with "tags" set to "Games,Story"
    And I use the "english" app, API version 2
    When I upload this generated program, API version 2
    Then the program should be tagged with "Games,Story" in the database

  Scenario: upload a tagged program with tags Geschichte and Musik on a german device
    Given I have a program with "tags" set to "Geschichte,Musik"
    And I use the "german" app, API version 2
    When I upload this generated program, API version 2
    Then the program should be tagged with "Geschichte,Musik" in the database

  Scenario: upload a tagged program with tags Music and Art on a device with no supported language
    Given I have a program with "tags" set to "Music,Art"
    And I use the "unknownLanguage" app, API version 2
    When I upload this generated program, API version 2
    Then the program should be tagged with "Music,Art" in the database

  Scenario: upload a tagged program with unknown tags
    Given I have a program with "tags" set to "Huhu,dontKnow"
    And I use the "english" app, API version 2
    When I upload this generated program, API version 2
    Then the program should not be tagged

  Scenario: upload a tagged program with more then three tags
    Given I have a program with "tags" set to "Games,Story,Music,Art"
    And I use the "english" app, API version 2
    When I upload this generated program, API version 2
    Then the program should be tagged with "Games,Story,Music" in the database

  Scenario: update a program with other tags
    Given I have a program with "tags" set to "Music,Art"
    And I use the "english" app, API version 2
    And I upload this generated program, API version 2
    When I upload this generated program again with the tags "Games,Story", API version 2
    Then the program should be tagged with "Games,Story" in the database
